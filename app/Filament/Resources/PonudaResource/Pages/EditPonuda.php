<?php

namespace App\Filament\Resources\PonudaResource\Pages;

use App\Filament\Resources\PonudaResource;
use App\Models\PonudaStavka;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditPonuda extends EditRecord
{
    protected static string $resource = PonudaResource::class;

    protected array $pendingStavke = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['stavke'] = $this->record->stavke->map(fn ($s) => $s->toArray())->values()->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingStavke = PonudaResource::filtrirajPrazneStavke($data['stavke'] ?? []);
        if ($this->pendingStavke === []) {
            throw ValidationException::withMessages(['stavke' => 'Ponuda mora imati barem jednu stavku.']);
        }
        unset($data['stavke']);

        return $data;
    }

    protected function afterSave(): void
    {
        $keepIds = [];
        foreach ($this->pendingStavke as $index => $stavka) {
            $data = [
                'ponuda_id' => $this->record->id,
                'usluga_id' => $stavka['usluga_id'] ?? null,
                'naziv' => $stavka['naziv'] ?? '',
                'opis' => $stavka['opis'] ?? null,
                'jedinica_mjere' => $stavka['jedinica_mjere'] ?? 'kom',
                'kolicina' => $stavka['kolicina'] ?? 1,
                'cijena' => $stavka['cijena'] ?? 0,
                'rabat_posto' => $stavka['rabat_posto'] ?? 0,
                'pdv_stopa' => $stavka['pdv_stopa'] ?? null,
                'ukupno' => $stavka['ukupno'] ?? 0,
                'redni_broj' => $index + 1,
            ];
            $id = $stavka['id'] ?? null;
            if ($id) {
                PonudaStavka::where('ponuda_id', $this->record->id)->where('id', $id)->update($data);
                $keepIds[] = (int) $id;
            } else {
                $keepIds[] = PonudaStavka::create($data)->id;
            }
        }
        $this->record->stavke()->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds))->delete();
        $this->record->load('stavke');
        $this->record->izracunajUkupno();
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->label('Obriši ponudu')];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
