<x-filament-panels::page>

    <div class="mb-4 flex items-center gap-3">
        <a href="{{ route('filament.admin.pages.obrasci', ['tenant' => $tvrtka->id]) }}"
           class="text-sm text-gray-500 hover:text-primary-600 flex items-center gap-1">
            <x-filament::icon icon="heroicon-o-arrow-left" class="h-4 w-4" />
            Natrag na obrasce
        </a>
    </div>

    {{-- Header --}}
    <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-950 dark:border-blue-800 p-5 mb-6 flex items-start justify-between gap-4">
        <div>
            <div class="text-lg font-bold text-blue-900 dark:text-blue-100">Obrazac PO-SD — {{ $godina }}.</div>
            <div class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                {{ $tvrtka->naziv }} &bull; OIB: {{ $tvrtka->oib }} &bull; {{ $tvrtka->vlasnik }}
            </div>
            <div class="text-xs text-blue-500 mt-1">Godišnja prijava paušalnog dohotka od samostalne djelatnosti</div>
        </div>
        <button wire:click="preuzmiPdf"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition shrink-0">
            <x-filament::icon icon="heroicon-o-document-arrow-down" class="h-4 w-4" />
            Preuzmi PDF
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Obračun --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Obračun poreza</h3>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr class="py-2">
                        <td class="py-2 text-gray-500">Ukupni primitci (IRA {{ $godina }}.)</td>
                        <td class="py-2 text-right font-semibold tabular-nums">{{ number_format($ukupniPrimitci, 2, ',', '.') }} €</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-500">Paušalni izdatci (30%)</td>
                        <td class="py-2 text-right font-semibold tabular-nums text-red-600">− {{ number_format($pausalni_izdatci, 2, ',', '.') }} €</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-700 dark:text-gray-300 font-medium">Dohodak</td>
                        <td class="py-2 text-right font-bold tabular-nums">{{ number_format($dohodak, 2, ',', '.') }} €</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-500">Osobni odbitak (godišnji)</td>
                        <td class="py-2 text-right tabular-nums text-red-600">− {{ number_format(6720, 2, ',', '.') }} €</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-700 dark:text-gray-300 font-medium">Porezna osnovica</td>
                        <td class="py-2 text-right font-bold tabular-nums">{{ number_format($poreznaOsnovica, 2, ',', '.') }} €</td>
                    </tr>
                    <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                        <td class="pt-3 pb-1 text-gray-900 dark:text-white font-semibold">Porez na dohodak</td>
                        <td class="pt-3 pb-1 text-right font-bold text-blue-600 text-base tabular-nums">{{ number_format($porezNaDohodak, 2, ',', '.') }} €</td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-4 text-xs text-gray-400">
                * Stope: 20% do 50.400 € / 30% iznad · Paušalni izdatci max 12.750 € · Osobni odbitak 560 €/mj<br>
                * Prirez se obračunava prema stopi Vaše općine/grada i nije uključen u ovaj izračun.
            </p>
        </div>

        {{-- Primitci po mjesecu --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Primitci po mjesecu</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase">
                        <th class="pb-2 text-left">Mjesec</th>
                        <th class="pb-2 text-right">Br. računa</th>
                        <th class="pb-2 text-right">Iznos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @php
                        $mjeseci = ['','Siječanj','Veljača','Ožujak','Travanj','Svibanj','Lipanj',
                                    'Srpanj','Kolovoz','Rujan','Listopad','Studeni','Prosinac'];
                    @endphp
                    @foreach ($racuniPoMjesecu as $m => $podaci)
                        <tr class="{{ $podaci['broj'] > 0 ? '' : 'opacity-40' }}">
                            <td class="py-1.5 text-gray-600 dark:text-gray-400">{{ $mjeseci[$m] }}</td>
                            <td class="py-1.5 text-right tabular-nums text-gray-500">{{ $podaci['broj'] }}</td>
                            <td class="py-1.5 text-right tabular-nums font-medium">{{ number_format($podaci['iznos'], 2, ',', '.') }} €</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-300 dark:border-gray-600 font-bold">
                        <td class="pt-2">Ukupno</td>
                        <td class="pt-2 text-right tabular-nums">{{ $brojRacuna }}</td>
                        <td class="pt-2 text-right tabular-nums text-blue-600">{{ number_format($ukupniPrimitci, 2, ',', '.') }} €</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

</x-filament-panels::page>
