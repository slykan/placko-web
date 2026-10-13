<div class="article-cta no-print">
    <h3>Računi bez muke — besplatno</h3>
    <p>plačko.app izdaje račune s HUB-3 barkodom, vodi knjigu izlaznih računa i sam izračuna PO-SD. Besplatno za paušaliste.</p>
    <a href="/admin/register" class="btn-primary">➜ Počni besplatno</a>
</div>

<div class="no-print">
    <h2 style="font-size:1.3rem;font-weight:800;margin:48px 0 0;">Ostali besplatni alati</h2>
    <div class="tool-grid" style="margin-top:20px;">
        @foreach ($ostali as $a)
            <a href="{{ url('/alati/'.$a['slug']) }}" class="tool-card" style="padding:22px;">
                <div class="ico">{{ $a['ico'] }}</div>
                <h2>{{ $a['title'] }}</h2>
            </a>
        @endforeach
    </div>
</div>
