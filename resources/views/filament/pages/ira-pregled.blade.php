<x-filament-panels::page>

    <div class="mb-4 flex items-center gap-3">
        <a href="{{ route('filament.admin.pages.obrasci', ['tenant' => $tvrtka->id]) }}"
           class="text-sm text-gray-500 hover:text-primary-600 flex items-center gap-1">
            <x-filament::icon icon="heroicon-o-arrow-left" class="h-4 w-4" />
            Natrag na obrasce
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 mb-6 flex items-center justify-between gap-4">
        <div>
            <div class="text-lg font-bold text-gray-900 dark:text-white">Knjiga IRA — {{ $godina }}.</div>
            <div class="text-sm text-gray-500 mt-0.5">{{ $tvrtka->naziv }} &bull; {{ $racuni->count() }} računa</div>
        </div>
        <button wire:click="preuzmiPdf"
            class="inline-flex items-center gap-2 rounded-lg bg-gray-700 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 transition shrink-0">
            <x-filament::icon icon="heroicon-o-document-arrow-down" class="h-4 w-4" />
            Preuzmi PDF
        </button>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">RB</th>
                    <th class="px-4 py-3 text-left">Broj računa</th>
                    <th class="px-4 py-3 text-left">Datum</th>
                    <th class="px-4 py-3 text-left">Klijent</th>
                    <th class="px-4 py-3 text-right">Osnovica</th>
                    <th class="px-4 py-3 text-right">Rabat</th>
                    <th class="px-4 py-3 text-right">PDV</th>
                    <th class="px-4 py-3 text-right font-bold">Ukupno</th>
                    <th class="px-4 py-3 text-center">Plaćeno</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                @forelse ($racuni as $i => $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-2 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-2 font-medium">{{ $r->broj }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $r->datum_izdavanja->format('d.m.Y.') }}</td>
                        <td class="px-4 py-2">{{ $r->klijent->naziv ?? '—' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float)$r->ukupno_osnovica, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right tabular-nums text-red-500">{{ number_format((float)$r->ukupno_rabat, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float)$r->ukupno_pdv, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right tabular-nums font-semibold">{{ number_format((float)$r->ukupno, 2, ',', '.') }} €</td>
                        <td class="px-4 py-2 text-center">
                            @if($r->placen_at)
                                <span class="text-green-600 text-xs">✓ {{ $r->placen_at->format('d.m.Y.') }}</span>
                            @else
                                <span class="text-red-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-400">Nema računa za {{ $godina }}. godinu</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-gray-800 font-bold text-sm border-t-2 border-gray-300 dark:border-gray-600">
                <tr>
                    <td colspan="4" class="px-4 py-3">UKUPNO</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($ukupnoOsnovica, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-red-500">{{ number_format($ukupnoRabat, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($ukupnoPdv, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-blue-600">{{ number_format($ukupno, 2, ',', '.') }} €</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

</x-filament-panels::page>
