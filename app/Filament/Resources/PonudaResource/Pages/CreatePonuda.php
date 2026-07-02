<?php

namespace App\Filament\Resources\PonudaResource\Pages;

use App\Filament\Resources\PonudaResource;
use App\Models\Ponuda;
use App\Models\PonudaStavka;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePonuda extends CreateRecord
{
    protected static string $resource = PonudaResource::class;

    protected array $pendingStavke = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tvrtkaId = filament()->getTenant()->id;
        $broj = Ponuda::generiraBroj($tvrtkaId);
        $data = array_merge($data, $broj, ['tvrtka_id' => $tvrtkaId]);
        $this->pendingStavke = PonudaResource::filtrirajPrazneStavke($data['stavke'] ?? []);
        if ($this->pendingStavke === []) {
            throw ValidationException::withMessages(['stavke' => 'Ponuda mora imati barem jednu stavku.']);
        }
        unset($data['stavke']);

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->pendingStavke as $index => $stavka) {
            PonudaStavka::create($this->stavkaData($stavka, $index));
        }
        $this->record->load('stavke');
        $this->record->izracunajUkupno();
    }

    private function stavkaData(array $stavka, int $index): array
    {
        return [
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
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
