<?php

namespace App\Filament\Resources\InventuraResource\Pages;

use App\Filament\Resources\InventuraResource;
use App\Models\InventuraStavka;
use App\Models\Usluga;
use App\Models\Zaliha;
use Filament\Resources\Pages\CreateRecord;

class CreateInventura extends CreateRecord
{
    protected static string $resource = InventuraResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tvrtka_id'] = filament()->getTenant()->id;
        $data['status'] = 'u_tijeku';

        return $data;
    }

    protected function afterCreate(): void
    {
        $tvrtkaId = filament()->getTenant()->id;

        $usluge = Usluga::where('tvrtka_id', $tvrtkaId)->where('prati_zalihu', true)->orderBy('naziv')->get();

        foreach ($usluge as $usluga) {
            $kolicina = (float) (Zaliha::where('usluga_id', $usluga->id)
                ->where('skladiste_id', $this->record->skladiste_id)
                ->value('kolicina') ?? 0);

            InventuraStavka::create([
                'inventura_id' => $this->record->id,
                'usluga_id' => $usluga->id,
                'ocekivana_kolicina' => $kolicina,
                'stvarna_kolicina' => null,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
