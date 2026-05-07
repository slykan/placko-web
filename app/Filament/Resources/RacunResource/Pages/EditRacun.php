<?php

namespace App\Filament\Resources\RacunResource\Pages;

use App\Filament\Resources\RacunResource;
use App\Models\RacunStavka;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

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

    protected array $stavkeIdsBefore = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->stavkeIdsBefore = $this->record->stavke()->pluck('id')->toArray();
        return $data;
    }

    protected function afterSave(): void
    {
        // Pokušaj dobiti keepIds iz form state (Livewire 3 čuva cijeli state)
        $keepIds = collect($this->data['stavke'] ?? [])
            ->map(fn ($item) => $item['id'] ?? null)
            ->filter()
            ->values()
            ->toArray();

        // Fallback: ako IDs nisu u state, obriši one koji nisu upravo touched
        if (empty($keepIds) && !empty($this->stavkeIdsBefore)) {
            $saveTime = Carbon::now()->subSeconds(2);
            RacunStavka::where('racun_id', $this->record->id)
                ->where('updated_at', '<', $saveTime)
                ->delete();
        } elseif (!empty($keepIds)) {
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
