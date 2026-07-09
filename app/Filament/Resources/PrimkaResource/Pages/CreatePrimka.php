<?php

namespace App\Filament\Resources\PrimkaResource\Pages;

use App\Filament\Resources\PrimkaResource;
use App\Models\Primka;
use App\Models\PrimkaStavka;
use App\Models\Usluga;
use App\Services\ZalihaService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePrimka extends CreateRecord
{
    protected static string $resource = PrimkaResource::class;

    protected array $pendingStavke = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tvrtkaId = filament()->getTenant()->id;
        $generirano = Primka::generiraBroj($tvrtkaId);

        $data['tvrtka_id'] = $tvrtkaId;
        $data['redni_broj'] = $generirano['redni_broj'];
        $data['godina'] = $generirano['godina'];
        $data['broj'] = $generirano['broj'];

        $this->pendingStavke = PrimkaResource::filtrirajPrazneStavke($data['stavke'] ?? []);
        if ($this->pendingStavke === []) {
            throw ValidationException::withMessages(['stavke' => 'Primka mora imati barem jednu stavku.']);
        }
        unset($data['stavke']);

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->pendingStavke as $index => $stavka) {
            PrimkaStavka::create([
                'primka_id' => $this->record->id,
                'usluga_id' => $stavka['usluga_id'] ?? null,
                'kolicina' => $stavka['kolicina'] ?? 1,
                'nabavna_cijena' => $stavka['nabavna_cijena'] ?? 0,
                'ukupno' => $stavka['ukupno'] ?? 0,
                'redni_broj' => $index + 1,
            ]);
        }

        $this->record->load(['stavke', 'skladiste']);
        $this->record->izracunajUkupno();

        foreach ($this->record->stavke as $stavka) {
            $usluga = Usluga::find($stavka->usluga_id);
            if (! $usluga || ! $usluga->prati_zalihu) {
                continue;
            }
            ZalihaService::zabiljezi(
                $usluga,
                $this->record->skladiste,
                'ulaz',
                (float) $stavka->kolicina,
                (float) $stavka->nabavna_cijena,
                ['primka_id' => $this->record->id]
            );
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
