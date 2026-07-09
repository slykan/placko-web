<?php

namespace App\Filament\Resources\PrimkaResource\Pages;

use App\Filament\Resources\PrimkaResource;
use App\Models\PrimkaStavka;
use App\Models\Usluga;
use App\Services\ZalihaService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditPrimka extends EditRecord
{
    protected static string $resource = PrimkaResource::class;

    protected array $pendingStavke = [];

    protected array $staroPoUsluzi = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['stavke'] = $this->record->stavke->map(fn ($s) => $s->toArray())->values()->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->staroPoUsluzi = $this->record->stavke
            ->groupBy('usluga_id')
            ->map(fn ($grupa) => (float) $grupa->sum('kolicina'))
            ->toArray();

        $this->pendingStavke = PrimkaResource::filtrirajPrazneStavke($data['stavke'] ?? []);
        if ($this->pendingStavke === []) {
            throw ValidationException::withMessages(['stavke' => 'Primka mora imati barem jednu stavku.']);
        }
        unset($data['stavke']);

        return $data;
    }

    protected function afterSave(): void
    {
        $keepIds = [];
        foreach ($this->pendingStavke as $index => $stavka) {
            $data = [
                'primka_id' => $this->record->id,
                'usluga_id' => $stavka['usluga_id'] ?? null,
                'kolicina' => $stavka['kolicina'] ?? 1,
                'nabavna_cijena' => $stavka['nabavna_cijena'] ?? 0,
                'ukupno' => $stavka['ukupno'] ?? 0,
                'redni_broj' => $index + 1,
            ];
            $id = $stavka['id'] ?? null;
            if ($id) {
                PrimkaStavka::where('primka_id', $this->record->id)->where('id', $id)->update($data);
                $keepIds[] = (int) $id;
            } else {
                $keepIds[] = PrimkaStavka::create($data)->id;
            }
        }
        $this->record->stavke()->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds))->delete();
        $this->record->load(['stavke', 'skladiste']);
        $this->record->izracunajUkupno();

        $novoPoUsluzi = $this->record->stavke
            ->groupBy('usluga_id')
            ->map(fn ($grupa) => (float) $grupa->sum('kolicina'))
            ->toArray();

        $sveUslugeId = array_unique(array_merge(array_keys($this->staroPoUsluzi), array_keys($novoPoUsluzi)));

        foreach ($sveUslugeId as $uslugaId) {
            if (! $uslugaId) {
                continue;
            }
            $staro = (float) ($this->staroPoUsluzi[$uslugaId] ?? 0);
            $novo = (float) ($novoPoUsluzi[$uslugaId] ?? 0);
            $delta = round($novo - $staro, 3);
            if (abs($delta) < 0.001) {
                continue;
            }
            $usluga = Usluga::find($uslugaId);
            if (! $usluga || ! $usluga->prati_zalihu) {
                continue;
            }
            $cijena = $this->record->stavke->firstWhere('usluga_id', (int) $uslugaId)?->nabavna_cijena;
            ZalihaService::zabiljezi(
                $usluga,
                $this->record->skladiste,
                'korekcija',
                $delta,
                $delta > 0 ? (float) $cijena : null,
                ['primka_id' => $this->record->id]
            );
        }
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->label('Obriši primku')];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
