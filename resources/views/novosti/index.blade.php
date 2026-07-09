@extends('layouts.blog', [
    'title' => 'Novosti',
    'description' => 'Novosti, savjeti i upute za korištenje plačko.app aplikacije za fakturiranje, ponude i računovodstvo.',
])

@section('content')
    <span class="page-eyebrow">Novosti</span>
    <h1 class="page-title">Novosti i upute</h1>
    <p class="page-sub">Savjeti, upute i najave novih mogućnosti u plačko.app aplikaciji.</p>

    <div class="post-grid">
        @foreach ($posts as $post)
            <a href="{{ route('novosti.show', $post['slug']) }}" class="post-card">
                <div class="post-meta">
                    <span>{{ \Illuminate\Support\Carbon::parse($post['date'])->translatedFormat('d.m.Y.') }}</span>
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
