<?php

namespace App\Filament\Resources\PretplataResource\Pages;

use App\Filament\Resources\PretplataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPretplata extends EditRecord
{
    protected static string $resource = PretplataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Obriši pretplatu'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['stavke_data'] = $this->record->stavke->map(fn ($s) => [
            'usluga_id' => $s->usluga_id,
            'naziv'     => $s->naziv,
            'opis'      => $s->opis,
            'kolicina'  => $s->kolicina,
            'cijena'    => $s->cijena,
            'pdv_stopa' => $s->pdv_stopa,
        ])->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->stavkeData = $data['stavke_data'] ?? [];
        unset($data['stavke_data']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->stavke()->delete();

        foreach ($this->stavkeData as $stavka) {
            $this->record->stavke()->create([
                'usluga_id' => $stavka['usluga_id'] ?? null,
                'naziv'     => $stavka['naziv'] ?? null,
                'opis'      => $stavka['opis'] ?? null,
                'kolicina'  => $stavka['kolicina'] ?? 1,
                'cijena'    => $stavka['cijena'] ?? 0,
                'pdv_stopa' => $stavka['pdv_stopa'] ?? null,
            ]);
        }

        $this->record->load('stavke');
        $this->record->izracunajUkupno();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
