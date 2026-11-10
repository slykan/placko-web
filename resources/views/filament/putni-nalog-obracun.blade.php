@php
    $o = $nalog?->obracun;
    $e = fn ($v) => number_format((float) $v, 2, ',', '.');
@endphp
@if (! $o)
    <p class="text-sm text-gray-500">Obračun će se prikazati nakon spremanja.</p>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200 dark:border-white/10">
                    <th class="py-2 pr-3 font-medium">Stavka</th>
                    <th class="py-2 pr-3 font-medium text-right">Količina</th>
                    <th class="py-2 pr-3 font-medium text-right">Iznos</th>
                    <th class="py-2 font-medium text-right">Ukupno €</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($o['dnevnice'] as $d)
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <td class="py-2 pr-3">{{ $d['opis'] }}<div class="text-xs text-gray-500">{{ $e($d['sati']) }} h</div></td>
                        <td class="py-2 pr-3 text-right">{{ rtrim(rtrim(number_format($d['broj'], 1, ',', '.'), '0'), ',') }} dnev.</td>
                        <td class="py-2 pr-3 text-right">{{ $e($d['jedinicni']) }} {{ $d['valuta'] }}</td>
                        <td class="py-2 text-right">
                            {{ $e($d['ukupno']) }}
                            @if ($d['valuta'] !== 'EUR')
                                <div class="text-xs text-gray-500">
                                    {{ $e($d['u_valuti']) }} {{ $d['valuta'] }}
                                    · {{ $o['tecaj'] ? 'tečaj '.number_format($o['tecaj'], 4, ',', '.') : 'nema tečaja, upiši ga ručno' }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if ($o['km'] > 0)
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <td class="py-2 pr-3">Kilometri{{ $nalog->registracija ? ' ('.$nalog->registracija.')' : '' }}@if ($nalog->prijevoz !== 'privatni_auto')<div class="text-xs text-gray-500">službeni/ostali prijevoz: bez naknade po km</div>@endif</td>
                        <td class="py-2 pr-3 text-right">{{ number_format($o['km'], 1, ',', '.') }} km</td>
                        <td class="py-2 pr-3 text-right">{{ $e($o['km_naknada']) }} €</td>
                        <td class="py-2 text-right">{{ $e($o['km_iznos']) }}</td>
                    </tr>
                @endif
                <tr class="border-b border-gray-100 dark:border-white/5">
                    <td class="py-2 pr-3">Ostali troškovi</td>
                    <td class="py-2 pr-3 text-right">{{ $nalog->troskovi->count() }} kom</td>
                    <td></td>
                    <td class="py-2 text-right">{{ $e($o['troskovi_ukupno']) }}</td>
                </tr>
                <tr><td class="pt-3 font-semibold" colspan="3">Ukupno</td><td class="pt-3 text-right font-semibold">{{ $e($o['ukupno']) }}</td></tr>
                @if ($o['predujam'] > 0)
                    <tr><td class="py-1" colspan="3">Predujam</td><td class="py-1 text-right">− {{ $e($o['predujam']) }}</td></tr>
                @endif
                <tr>
                    <td class="pt-2 text-base font-bold" colspan="3">{{ $o['za_isplatu'] < 0 ? 'Za povrat (preplaćeni predujam)' : 'Za isplatu' }}</td>
                    <td class="pt-2 text-right text-base font-bold text-primary-600">{{ $e(abs($o['za_isplatu'])) }} €</td>
                </tr>
            </tbody>
        </table>
        @if ($o['obroci'] > 0)
            <p class="mt-2 text-xs text-gray-500">Dnevnice umanjene za {{ $o['obroci'] * 30 }} % zbog osiguranih obroka.</p>
        @endif
    </div>
@endif
