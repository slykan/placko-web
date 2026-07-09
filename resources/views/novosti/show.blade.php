@extends('layouts.blog', [
    'title' => $post['title'],
    'description' => $post['excerpt'],
])

@section('content')
    <a href="{{ route('novosti.index') }}" class="article-back">← Sve novosti</a>

    <span class="page-eyebrow">Novosti</span>
    <h1 class="page-title">{{ $post['title'] }}</h1>
    <div class="post-meta" style="margin-bottom: 32px;">
        <span>{{ \Illuminate\Support\Carbon::parse($post['date'])->translatedFormat('d.m.Y.') }}</span>
        <span>·</span>
        <span>{{ $post['read'] }}</span>
    </div>

    <article class="post-body">
        @include('novosti.posts.'.$post['slug'])
    </article>

    <div class="article-cta">
        <h3>Isprobaj plačko.app besplatno</h3>
        <p>Izrađuj ponude, šalji ih klijentima i pretvaraj u račune jednim klikom.</p>
        <a href="/admin/register" class="btn-primary">➜ Počni besplatno</a>
    </div>
@endsection
