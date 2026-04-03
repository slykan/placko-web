<x-filament-widgets::widget>
    <x-filament::section :heading="$heading">
        @php
            $radius   = 60;
            $stroke   = 22;
            $cx       = 80;
            $cy       = 80;
            $circumference = 2 * M_PI * $radius;
            $offset   = 0;
            $hasData  = $total > 0;
        @endphp

        <div class="flex items-center gap-6">
            {{-- SVG Donut --}}
            <svg width="160" height="160" viewBox="0 0 160 160" class="shrink-0">
                @if (!$hasData)
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $radius }}"
                        fill="none" stroke="#e5e7eb" stroke-width="{{ $stroke }}" />
                @else
                    @foreach ($segments as $seg)
                        @php
                            $pct  = $total > 0 ? $seg['value'] / $total : 0;
                            $dash = $pct * $circumference;
                            $gap  = $circumference - $dash;
                        @endphp
                        <circle
                            cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $radius }}"
                            fill="none"
                            stroke="{{ $seg['color'] }}"
                            stroke-width="{{ $stroke }}"
                            stroke-dasharray="{{ round($dash, 2) }} {{ round($gap, 2) }}"
                            stroke-dashoffset="{{ round(-$offset * $circumference / 360 * 360 + $circumference / 4, 2) }}"
                            style="transform: rotate(-90deg); transform-origin: {{ $cx }}px {{ $cy }}px;"
                        />
                        @php $offset += $pct * 360; @endphp
                    @endforeach
                @endif
                {{-- Sredina --}}
                <text x="{{ $cx }}" y="{{ $cy - 6 }}" text-anchor="middle"
                    font-size="22" font-weight="bold" fill="currentColor">{{ $total }}</text>
                <text x="{{ $cx }}" y="{{ $cy + 14 }}" text-anchor="middle"
                    font-size="11" fill="#94a3b8">ukupno</text>
            </svg>

            {{-- Legenda --}}
            <div class="flex flex-col gap-2 flex-1">
                @foreach ($segments as $seg)
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full shrink-0"
                                  style="background-color: {{ $seg['color'] }}"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $seg['label'] }}</span>
                        </div>
                        <span class="text-sm font-semibold tabular-nums">{{ $seg['value'] }}</span>
                    </div>
                    {{-- Progress bar --}}
                    <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-700 -mt-1">
                        <div class="h-1.5 rounded-full"
                             style="width: {{ $total > 0 ? round($seg['value'] / $total * 100) : 0 }}%; background-color: {{ $seg['color'] }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
