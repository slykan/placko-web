@extends('moji-nalozi.layout')

@section('title', 'Moji nalozi')

@section('content')
@php
    $statusi = \App\Models\RadniNalog::statusi();
    $token = $zaposlenik->portal_token;
@endphp

<h1 style="margin-bottom:12px;">Otvoreni nalozi <span class="muted">({{ $otvoreni->count() }})</span></h1>
<div class="list">
    @forelse($otvoreni as $n)
        <a class="item" href="{{ route('moji-nalozi.show', [$token, $n->id]) }}">
            <div class="row1">
                <strong>{{ $n->broj }}</strong>
                @if(! $n->pivot->prihvacen_at)
                    <span class="badge" style="background:#ffedd5;color:#9a3412;">Novo — prihvati</span>
                @else
                    <span class="badge b-{{ $n->status }}">{{ $statusi[$n->status] ?? $n->status }}</span>
                @endif
            </div>
            <div style="margin-top:4px;font-weight:600;">{{ $n->klijent?->naziv ?? 'Interni posao' }}@if($n->predmet) · {{ $n->predmet }}@endif</div>
            <div class="muted">
                @if($n->lokacija){{ $n->lokacija }}<br>@endif
                @if($n->rok)
                    <span class="{{ $n->rok->isPast() && ! $n->rok->isToday() ? 'late' : '' }}">Rok: {{ $n->rok->format('d.m.Y.') }}</span>
                @endif
            </div>
        </a>
    @empty
        <div class="card muted">Trenutno nemaš otvorenih naloga.</div>
    @endforelse
</div>

@if($putni->isNotEmpty())
    <h2 style="margin-top:22px;">Putni nalozi</h2>
    <div class="list">
        @foreach($putni as $p)
            <a class="item" href="{{ route('moji-nalozi.putni', [$token, $p->id]) }}">
                <div class="row1">
                    <strong>{{ $p->broj }}</strong>
                    <span class="badge {{ $p->status === 'otvoren' ? 'b-u_tijeku' : 'b-zavrsen' }}">{{ $p->status === 'otvoren' ? 'Upiši podatke' : 'Obračunat' }}</span>
                </div>
                <div style="margin-top:4px;font-weight:600;">{{ $p->odrediste }}@if($p->vrsta === 'inozemni') · {{ $p->drzava }}@endif</div>
                <div class="muted">{{ $p->polazak_at?->format('d.m.Y. H:i') }}@if($p->registracija) · {{ $p->registracija }}@endif</div>
            </a>
        @endforeach
    </div>
@endif

@if($zatvoreni->isNotEmpty())
    <h2 style="margin-top:22px;">Zatvoreni</h2>
    <div class="list">
        @foreach($zatvoreni as $n)
            <a class="item" href="{{ route('moji-nalozi.show', [$token, $n->id]) }}" style="opacity:.85;">
                <div class="row1">
                    <strong>{{ $n->broj }}</strong>
                    <span class="badge b-{{ $n->status }}">{{ $statusi[$n->status] ?? $n->status }}</span>
                </div>
                <div class="muted">{{ $n->klijent?->naziv ?? 'Interni posao' }}@if($n->predmet) · {{ $n->predmet }}@endif
                    @if($n->zavrsen_at) · {{ $n->zavrsen_at->format('d.m.Y.') }}@endif</div>
            </a>
        @endforeach
    </div>
@endif
@endsection
