<?php

namespace App\Filament\Resources\RacunResource\Pages;

use App\Filament\Resources\RacunResource;
use App\Models\RacunStavka;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditRacun extends EditRecord
{
    protected static string $resource = RacunResource::class;

    protected array $pendingStavke = [];

    protected array $deltaPoUsluzi = [];

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['stavke'] = $this->record->stavke
            ->map(fn ($s) => $s->toArray())
            ->values()
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $staroPoUsluzi = $this->record->stavke
            ->groupBy('usluga_id')
            ->map(fn ($grupa) => (float) $grupa->sum('kolicina'))
            ->toArray();

        $this->pendingStavke = RacunResource::filtrirajPrazneStavke($data['stavke'] ?? []);

        if (empty($this->pendingStavke)) {
            throw ValidationException::withMessages([
                'stavke' => 'Račun mora imati barem jednu stavku.',
            ]);
        }

        $novoPoUsluzi = [];
        foreach ($this->pendingStavke as $stavka) {
            if (empty($stavka['usluga_id'])) {
                continue;
            }
            $uslugaId = $stavka['usluga_id'];
            $novoPoUsluzi[$uslugaId] = ($novoPoUsluzi[$uslugaId] ?? 0) + (float) ($stavka['kolicina'] ?? 0);
        }

        foreach (array_unique(array_merge(array_keys($staroPoUsluzi), array_keys($novoPoUsluzi))) as $uslugaId) {
            $this->deltaPoUsluzi[$uslugaId] = ($novoPoUsluzi[$uslugaId] ?? 0) - ($staroPoUsluzi[$uslugaId] ?? 0);
        }
        RacunResource::provjeriDovoljnoZalihe(filament()->getTenant()->id, $this->deltaPoUsluzi);

        unset($data['stavke']);

        return $data;
    }

    protected function afterSave(): void
    {
        $keepIds = [];

        foreach ($this->pendingStavke as $index => $stavka) {
            $id = $stavka['id'] ?? null;

            $stavkaData = [
                'racun_id'       => $this->record->id,
                'usluga_id'      => $stavka['usluga_id'] ?? null,
                'naziv'          => $stavka['naziv'] ?? '',
                'opis'           => $stavka['opis'] ?? null,
                'jedinica_mjere' => $stavka['jedinica_mjere'] ?? 'kom',
                'kolicina'       => $stavka['kolicina'] ?? 1,
                'cijena'         => $stavka['cijena'] ?? 0,
                'rabat_posto'    => $stavka['rabat_posto'] ?? 0,
                'pdv_stopa'      => $stavka['pdv_stopa'] ?? null,
                'ukupno'         => $stavka['ukupno'] ?? 0,
                'redni_broj'     => $index + 1,
            ];

            if ($id) {
                RacunStavka::where('racun_id', $this->record->id)
                    ->where('id', $id)
                    ->update($stavkaData);
                $keepIds[] = (int) $id;
            } else {
                $new = RacunStavka::create($stavkaData);
                $keepIds[] = $new->id;
            }
        }

        $this->record->stavke()
            ->when(! empty($keepIds), fn ($q) => $q->whereNotIn('id', $keepIds))
            ->delete();

        $this->record->load('stavke');
        $this->record->izracunajUkupno();

        RacunResource::primijeniProdajuNaZalihu($this->record, $this->deltaPoUsluzi, 'korekcija');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Obriši račun'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
