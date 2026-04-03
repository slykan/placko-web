<x-filament-panels::page>

    {{-- Vrsta poslovanja info --}}
    <div class="mb-6 flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <x-filament::icon icon="heroicon-o-building-office" class="h-6 w-6 text-primary-500" />
        <div>
            <div class="font-semibold text-gray-900 dark:text-white">{{ $tvrtka->naziv }}</div>
            <div class="text-sm text-gray-500">
                Vrsta poslovanja: <span class="font-medium text-primary-600">{{ \App\Models\Tvrtka::vrstePoslovanja()[$vrsta] ?? $vrsta }}</span>
                &mdash; prikazani obrasci odnose se na ovu vrstu poslovanja
            </div>
        </div>
    </div>

    {{-- Obrasci grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($dostupniObrasci as $key => $obrazac)
            @php
                $boja = $obrazac['boja'];
                $bgStyle   = match($boja) {
                    'blue'   => 'background:#dbeafe',
                    'purple' => 'background:#f3e8ff',
                    'orange' => 'background:#ffedd5',
                    'green'  => 'background:#dcfce7',
                    default  => 'background:#f3f4f6',
                };
                $iconColor = match($boja) {
                    'blue'   => '#2563eb',
                    'purple' => '#9333ea',
                    'orange' => '#ea580c',
                    'green'  => '#16a34a',
                    default  => '#6b7280',
                };
                $btnStyle = match($boja) {
                    'blue'   => 'background:#2563eb',
                    'purple' => 'background:#9333ea',
                    'orange' => 'background:#ea580c',
                    'green'  => 'background:#16a34a',
                    default  => 'background:#4b5563',
                };
            @endphp

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 flex flex-col gap-3">
                {{-- Header --}}
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" style="{{ $bgStyle }}">
                        <x-filament::icon :icon="$obrazac['ikona']" class="h-5 w-5" :style="'color:'.$iconColor" />
                    </div>
                    <div class="flex-1">
                        <div class="text-base font-bold text-gray-900 dark:text-white">{{ $obrazac['naziv'] }}</div>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $obrazac['opis'] }}</p>
                        <p class="mt-1 text-xs text-gray-400">
                            <span class="font-medium">Rok predaje:</span> {{ $obrazac['rok'] }}
                        </p>
                    </div>
                </div>

                {{-- Akcija --}}
                <div class="border-t border-gray-100 dark:border-gray-800 pt-3 flex flex-wrap gap-2">
                    @if ($obrazac['akcija'] === 'po_sd')
                        @foreach ($godine as $god)
                            <a href="{{ route('filament.admin.pages.po-sd', ['tenant' => $tvrtka->id, 'godina' => $god]) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-white"
                               style="{{ $btnStyle }}">
                                ↓ PO-SD {{ $god }}.
                            </a>
                        @endforeach

                    @elseif ($obrazac['akcija'] === 'ira')
                        @foreach ($godine as $god)
                            <a href="{{ route('filament.admin.pages.ira-pregled', ['tenant' => $tvrtka->id, 'godina' => $god]) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-white"
                               style="{{ $btnStyle }}">
                                ↓ IRA {{ $god }}.
                            </a>
                        @endforeach

                    @else
                        <span class="text-xs text-gray-400 italic">Automatska generacija ovog obrasca uskoro</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</x-filament-panels::page>
