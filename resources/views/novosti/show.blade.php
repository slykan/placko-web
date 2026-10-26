@extends('layouts.blog', [
    'title' => $post['title'],
    'description' => $post['excerpt'],
    'nav' => 'novosti',
    'ads' => $post['kategorija'] === 'vodic', // ads only next to guides, not app news
    'jsonLd' => [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post['title'],
        'description' => $post['excerpt'],
        'datePublished' => $post['date'],
        'dateModified' => $post['modified'] ?? $post['date'],
        'inLanguage' => 'hr',
        'author' => ['@type' => 'Organization', 'name' => 'plačko.app', 'url' => url('/o-nama')],
        'publisher' => ['@type' => 'Organization', 'name' => 'plačko.app', 'logo' => ['@type' => 'ImageObject', 'url' => url('/img/icon-512.png')]],
        'mainEntityOfPage' => url()->current(),
    ],
])

@section('content')
    <a href="{{ route('novosti.index') }}" class="article-back">← Sve novosti i vodiči</a>

    <span class="page-eyebrow">{{ \App\Http\Controllers\BlogController::KATEGORIJE[$post['kategorija']] }}</span>
    <h1 class="page-title">{{ $post['title'] }}</h1>
    <div class="post-meta" style="margin-bottom: 32px;">
        <span>Objavljeno {{ \Illuminate\Support\Carbon::parse($post['date'])->format('d.m.Y.') }}</span>
        <span>·</span>
        <span>{{ $post['read'] }}</span>
        <span>·</span>
        <span>Uredništvo plačko.app</span>
    </div>

    <article class="post-body">
        @include('novosti.posts.'.$post['slug'])
    </article>

    @if ($post['kategorija'] === 'vodic')
        <p class="sources" style="border:0;">
            Vodič je informativnog karaktera i ne zamjenjuje savjet knjigovođe ili Porezne uprave. Propisi se mijenjaju —
            ako primijetiš da nešto više nije točno, javi nam se na <a href="/kontakt">stranici Kontakt</a>.
        </p>
    @endif

    <div class="article-cta">
        <h3>Isprobaj plačko.app besplatno</h3>
        <p>Računi, ponude, HUB-3 barkod, PO-SD i knjiga izlaznih računa — besplatno za paušalne obrtnike.</p>
        <a href="/admin/register" class="btn-primary">➜ Počni besplatno</a>
    </div>

    @if (! empty($ostale))
        <h2 style="font-size:1.3rem;font-weight:800;margin:48px 0 0;">Pročitaj i ovo</h2>
        <div class="post-grid" style="margin-top:20px;">
            @foreach ($ostale as $o)
                <a href="{{ route('novosti.show', $o['slug']) }}" class="post-card" style="padding:24px;">
                    <div class="post-meta"><span class="badge badge-{{ $o['kategorija'] }}">{{ \App\Http\Controllers\BlogController::KATEGORIJE[$o['kategorija']] }}</span></div>
                    <h2 style="font-size:1.1rem;">{{ $o['title'] }}</h2>
                </a>
            @endforeach
        </div>
    @endif
@endsection
