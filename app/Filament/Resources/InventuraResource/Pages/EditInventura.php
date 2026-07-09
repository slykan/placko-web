<?php

namespace App\Filament\Resources\InventuraResource\Pages;

use App\Filament\Resources\InventuraResource;
use App\Models\InventuraStavka;
use App\Models\Usluga;
use App\Services\ZalihaService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInventura extends EditRecord
{
    protected static string $resource = InventuraResource::class;

    protected array $pendingStavke = [];

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        if ($this->record->status === 'zavrsena') {
            Notification::make()
                ->title('Inventura je zaključena i ne može se uređivati.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['stavke'] = $this->record->stavke()->with('usluga')->get()
            ->map(fn (InventuraStavka $s) => [
                'id' => $s->id,
                'usluga_id' => $s->usluga_id,
                'naziv' => $s->usluga->naziv ?? '',
                'ocekivana_kolicina' => $s->ocekivana_kolicina,
                'stvarna_kolicina' => $s->stvarna_kolicina,
                'razlika' => $s->stvarna_kolicina !== null
                    ? round((float) $s->stvarna_kolicina - (float) $s->ocekivana_kolicina, 3)
                    : null,
            ])
            ->values()
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingStavke = $data['stavke'] ?? [];
        unset($data['stavke']);

        return $data;
    }

    protected function afterSave(): void
    {
        foreach ($this->pendingStavke as $stavka) {
            if (empty($stavka['id'])) {
                continue;
            }
            InventuraStavka::where('inventura_id', $this->record->id)
                ->where('id', $stavka['id'])
                ->update(['stvarna_kolicina' => $stavka['stvarna_kolicina'] ?? null]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('zakljuci')
                ->label('Zaključi inventuru')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'u_tijeku')
                ->requiresConfirmation()
                ->modalHeading('Zaključi inventuru')
                ->modalDescription('Za sve stavke s unesenim stvarnim stanjem i razlikom, zaliha će se korigirati. Ova radnja se ne može poništiti.')
                ->action(function () {
                    $this->record->load(['stavke.usluga', 'skladiste']);

                    foreach ($this->record->stavke as $stavka) {
                        if ($stavka->stvarna_kolicina === null) {
                            continue;
                        }

                        $razlika = round((float) $stavka->stvarna_kolicina - (float) $stavka->ocekivana_kolicina, 3);
                        if (abs($razlika) < 0.001) {
                            continue;
                        }

                        $usluga = $stavka->usluga ?? Usluga::find($stavka->usluga_id);
                        if (! $usluga) {
                            continue;
                        }

                        ZalihaService::zabiljezi(
                            $usluga,
                            $this->record->skladiste,
                            'korekcija',
                            $razlika,
                            null,
                            ['inventura_id' => $this->record->id]
                        );
                    }

                    $this->record->update([
                        'status' => 'zavrsena',
                        'zavrsena_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Inventura zaključena')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            Actions\DeleteAction::make()->label('Obriši inventuru'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
