<?php

namespace App\Filament\Resources\RacunResource\Pages;

use App\Filament\Resources\RacunResource;
use App\Models\RacunStavka;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRacun extends EditRecord
{
    protected static string $resource = RacunResource::class;

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        if ($this->record->fiskaliziran_at) {
            Notification::make()
                ->title('Račun je fiskaliziran i ne može se uređivati.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Obriši račun'),
        ];
    }

    protected function afterSave(): void
    {
        $keepIds = collect($this->data['stavke'] ?? [])
            ->map(fn ($item) => $item['id'] ?? null)
            ->filter()
            ->values()
            ->toArray();

        if (count($keepIds) > 0) {
            RacunStavka::where('racun_id', $this->record->id)
                ->whereNotIn('id', $keepIds)
                ->delete();
        }

        $this->record->load('stavke');
        $this->record->izracunajUkupno();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
