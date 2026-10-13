@extends('layouts.blog', [
    'title' => $kategorija === 'vodic' ? 'Vodiči za paušaliste i obrtnike' : 'Novosti i vodiči',
    'description' => 'Vodiči za paušalne obrtnike (porez, doprinosi, fiskalizacija, eRačun, sidrene cijene) i novosti iz plačko.app aplikacije za fakturiranje.',
    'nav' => 'novosti',
])

@section('content')
    <span class="page-eyebrow">Novosti i vodiči</span>
    <h1 class="page-title">{{ $kategorija === 'vodic' ? 'Vodiči za obrtnike' : 'Novosti i vodiči' }}</h1>
    <p class="page-sub">Praktični vodiči za paušaliste i male obrtnike — porez, doprinosi, fiskalizacija, eRačun — i sve što je novo u plačko.app.</p>

    <div class="post-filter">
        <a href="{{ route('novosti.index') }}" class="{{ $kategorija === null ? 'on' : '' }}">Sve</a>
        <a href="{{ route('novosti.index', ['kategorija' => 'vodic']) }}" class="{{ $kategorija === 'vodic' ? 'on' : '' }}">Vodiči</a>
        <a href="{{ route('novosti.index', ['kategorija' => 'novosti']) }}" class="{{ $kategorija === 'novosti' ? 'on' : '' }}">Novo u aplikaciji</a>
        <a href="/alati">Besplatni alati →</a>
    </div>

    <div class="post-grid">
        @foreach ($posts as $post)
            <a href="{{ route('novosti.show', $post['slug']) }}" class="post-card">
                <div class="post-meta">
                    <span class="badge badge-{{ $post['kategorija'] }}">{{ \App\Http\Controllers\BlogController::KATEGORIJE[$post['kategorija']] }}</span>
                    <span>{{ \Illuminate\Support\Carbon::parse($post['date'])->format('d.m.Y.') }}</span>
                    <span>·</span>
                    <span>{{ $post['read'] }}</span>
                </div>
                <h2>{{ $post['title'] }}</h2>
                <p>{{ $post['excerpt'] }}</p>
                <span class="post-read-more">Pročitaj više →</span>
            </a>
        @endforeach
    </div>
@endsection
