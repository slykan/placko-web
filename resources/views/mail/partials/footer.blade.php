@php
    $adresaLinija = collect([$tvrtka?->adresa, $tvrtka?->mjesto])->filter()->implode(', ');
    $kontaktLinija = collect([$tvrtka?->email, $tvrtka?->kontakt_broj])->filter()->implode(' · ');
@endphp
<tr>
    <td style="background:#f7fafa;border-top:1px solid #e2ece9;padding:22px 32px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#4b5563;line-height:1.7;" align="left">
        <strong style="color:#162433;">{{ $tvrtka?->naziv }}</strong><br>
        @if($adresaLinija)
            {{ $adresaLinija }}<br>
        @endif
        @if($tvrtka?->oib)
            OIB: {{ $tvrtka->oib }}
        @endif
        @if($kontaktLinija)
            @if($tvrtka?->oib) &nbsp;·&nbsp; @endif{{ $kontaktLinija }}
        @endif
        @if($tvrtka?->web_mjesto)
            <br>{{ $tvrtka->web_mjesto }}
        @endif
    </td>
</tr>
