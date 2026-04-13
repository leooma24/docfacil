<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $article['title'] }} — DocFácil Blog</title>
    <meta name="description" content="{{ $article['description'] }}">
    <meta property="og:title" content="{{ $article['title'] }}">
    <meta property="og:description" content="{{ $article['description'] }}">
    <meta property="og:url" content="{{ url("/blog/{$slug}") }}">
    <meta property="og:type" content="article">
    <link rel="canonical" href="{{ url("/blog/{$slug}") }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "{{ $article['title'] }}",
        "description": "{{ $article['description'] }}",
        "datePublished": "{{ $article['date'] }}",
        "author": { "@@type": "Organization", "name": "DocFácil" },
        "publisher": { "@@type": "Organization", "name": "DocFácil", "url": "{{ url('/') }}" }
    }
    </script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#fff; color:#1e293b; }
        .nav { background:white; border-bottom:1px solid #e2e8f0; padding:16px 0; position:sticky; top:0; z-index:50; }
        .container { max-width:760px; margin:0 auto; padding:0 20px; }
        .container-wide { max-width:1100px; margin:0 auto; padding:0 20px; }
        .nav-inner { display:flex; align-items:center; justify-content:space-between; }
        .nav-logo { font-size:1.2rem; font-weight:800; color:#0d9488; text-decoration:none; }
        .nav-links a { font-size:0.85rem; color:#64748b; text-decoration:none; margin-left:20px; font-weight:600; }
        .nav-links a:hover { color:#0d9488; }

        .article-hero { padding:48px 0 32px; border-bottom:1px solid #f1f5f9; margin-bottom:36px; }
        .article-cat { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#0d9488; margin-bottom:12px; display:inline-block; background:#f0fdfa; padding:4px 12px; border-radius:999px; }
        .article-title { font-size:2rem; font-weight:800; letter-spacing:-0.02em; color:#0f172a; line-height:1.2; }
        .article-desc { font-size:1.05rem; color:#64748b; margin-top:12px; line-height:1.6; }
        .article-meta { display:flex; gap:16px; margin-top:16px; font-size:0.8rem; color:#94a3b8; font-weight:600; }

        .article-body { padding-bottom:48px; }
        .article-body p { font-size:1rem; line-height:1.8; color:#374151; margin-bottom:20px; }
        .article-body h2 { font-size:1.4rem; font-weight:800; color:#0f172a; margin:32px 0 12px; letter-spacing:-0.01em; }
        .article-body h3 { font-size:1.1rem; font-weight:700; color:#0f172a; margin:24px 0 8px; }

        .article-cta {
            background:linear-gradient(135deg,#0d9488,#0891b2);
            color:white; border-radius:1rem; padding:28px 32px;
            margin:32px 0; text-align:center;
        }
        .article-cta p { color:white !important; font-size:1.05rem; font-weight:600; margin-bottom:16px; }
        .article-cta a {
            display:inline-block; background:white; color:#0d9488;
            padding:12px 28px; border-radius:10px; font-weight:800;
            font-size:0.95rem; text-decoration:none; transition:transform 0.2s;
        }
        .article-cta a:hover { transform:translateY(-2px); }

        .related { padding:48px 0; background:#f8fafc; border-top:1px solid #e2e8f0; }
        .related h2 { font-size:1.3rem; font-weight:800; color:#0f172a; margin-bottom:24px; text-align:center; }
        .related-grid { display:grid; grid-template-columns:1fr; gap:20px; }
        @media (min-width:768px) { .related-grid { grid-template-columns:repeat(2,1fr); } }
        .related-card { background:white; border:1px solid #e2e8f0; border-radius:1rem; padding:24px; text-decoration:none; color:inherit; transition:all 0.3s; }
        .related-card:hover { border-color:#99f6e4; box-shadow:0 8px 24px rgba(13,148,136,0.1); }
        .related-card-title { font-size:1rem; font-weight:700; color:#0f172a; margin-bottom:6px; }
        .related-card-desc { font-size:0.85rem; color:#64748b; }

        .footer { background:#0f172a; color:#94a3b8; padding:32px 0; text-align:center; font-size:0.8rem; }
        .footer a { color:#5eead4; text-decoration:none; }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="container-wide nav-inner">
            <a href="/" class="nav-logo">DocFácil</a>
            <div class="nav-links">
                <a href="/">Inicio</a>
                <a href="/blog">Blog</a>
                <a href="/doctor/register">Registrarse</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="article-hero">
            <span class="article-cat">{{ $article['category'] }}</span>
            <h1 class="article-title">{{ $article['title'] }}</h1>
            <p class="article-desc">{{ $article['description'] }}</p>
            <div class="article-meta">
                <span>{{ \Carbon\Carbon::parse($article['date'])->translatedFormat('d \d\e F, Y') }}</span>
                <span>·</span>
                <span>{{ $article['read_time'] }} lectura</span>
            </div>
        </div>

        <div class="article-body">
            @foreach($article['content'] as $block)
                @if($block['type'] === 'p')
                    <p>{{ $block['text'] }}</p>
                @elseif($block['type'] === 'h2')
                    <h2>{{ $block['text'] }}</h2>
                @elseif($block['type'] === 'h3')
                    <h3>{{ $block['text'] }}</h3>
                @elseif($block['type'] === 'cta')
                    <div class="article-cta">
                        <p>{{ $block['text'] }}</p>
                        <a href="/doctor/register">Empezar gratis →</a>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    @if(count($related))
    <div class="related">
        <div class="container-wide">
            <h2>También te puede interesar</h2>
            <div class="related-grid">
                @foreach($related as $rSlug => $r)
                <a href="/blog/{{ $rSlug }}" class="related-card">
                    <div class="related-card-title">{{ $r['title'] }}</div>
                    <div class="related-card-desc">{{ $r['description'] }}</div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <footer class="footer">
        <div class="container-wide">
            &copy; {{ date('Y') }} DocFácil — <a href="/">Software para consultorios médicos y dentales</a>
        </div>
    </footer>
</body>
</html>
