<?php

namespace App\Filament\Resources\KorisnikResource\Pages;

use App\Filament\Resources\KorisnikResource;
use Filament\Resources\Pages\EditRecord;

class EditKorisnik extends EditRecord
{
    protected static string $resource = KorisnikResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Admin ne može sam sebi ukinuti administratorska prava
        if ($this->record->is(auth()->user())) {
            $data['is_admin'] = true;
        }

        return $data;
    }

    /** plan/plan_do/is_admin namjerno nisu u $fillable modela (da ih ne može postaviti registracija ili profil). */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $record->forceFill([
            'plan' => $data['plan'],
            'plan_do' => $data['plan_do'] ?? null,
            'is_admin' => (bool) ($data['is_admin'] ?? $record->is_admin),
        ])->save();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
