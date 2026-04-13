<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blog — DocFácil · Software para consultorios médicos</title>
    <meta name="description" content="Artículos para médicos y dentistas: gestión de consultorio, recordatorios, expedientes digitales, recetas electrónicas y más.">
    <meta property="og:title" content="Blog — DocFácil">
    <meta property="og:description" content="Tips para médicos y dentistas que quieren digitalizar su consultorio.">
    <meta property="og:url" content="{{ url('/blog') }}">
    <link rel="canonical" href="{{ url('/blog') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f8fafc; color:#1e293b; }
        .nav { background:white; border-bottom:1px solid #e2e8f0; padding:16px 0; position:sticky; top:0; z-index:50; }
        .container { max-width:1100px; margin:0 auto; padding:0 20px; }
        .nav-inner { display:flex; align-items:center; justify-content:space-between; }
        .nav-logo { font-size:1.2rem; font-weight:800; color:#0d9488; text-decoration:none; }
        .nav-links a { font-size:0.85rem; color:#64748b; text-decoration:none; margin-left:20px; font-weight:600; }
        .nav-links a:hover { color:#0d9488; }

        .hero { padding:60px 0 40px; text-align:center; }
        .hero h1 { font-size:2.5rem; font-weight:800; letter-spacing:-0.02em; color:#0f172a; }
        .hero p { font-size:1.1rem; color:#64748b; margin-top:8px; max-width:600px; margin-left:auto; margin-right:auto; }

        .grid { display:grid; grid-template-columns:1fr; gap:28px; padding-bottom:60px; }
        @media (min-width:768px) { .grid { grid-template-columns:repeat(2,1fr); } }
        @media (min-width:1024px) { .grid { grid-template-columns:repeat(3,1fr); } }

        .card { background:white; border-radius:1.25rem; overflow:hidden; border:1px solid #e2e8f0; transition:all 0.3s; text-decoration:none; color:inherit; display:flex; flex-direction:column; }
        .card:hover { transform:translateY(-4px); box-shadow:0 12px 40px rgba(13,148,136,0.12); border-color:#99f6e4; }
        .card-img { height:180px; background:linear-gradient(135deg,#0d9488,#0891b2); display:flex; align-items:center; justify-content:center; font-size:4rem; }
        .card-body { padding:24px; flex:1; display:flex; flex-direction:column; }
        .card-cat { font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#0d9488; margin-bottom:8px; }
        .card-title { font-size:1.15rem; font-weight:800; color:#0f172a; letter-spacing:-0.01em; line-height:1.3; margin-bottom:10px; }
        .card-desc { font-size:0.85rem; color:#64748b; line-height:1.6; flex:1; }
        .card-meta { display:flex; gap:12px; margin-top:14px; font-size:0.75rem; color:#94a3b8; font-weight:600; }

        .footer { background:#0f172a; color:#94a3b8; padding:32px 0; text-align:center; font-size:0.8rem; }
        .footer a { color:#5eead4; text-decoration:none; }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="container nav-inner">
            <a href="/" class="nav-logo">DocFácil</a>
            <div class="nav-links">
                <a href="/">Inicio</a>
                <a href="/blog">Blog</a>
                <a href="/doctor/register">Registrarse</a>
                <a href="/doctor/login">Entrar</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <h1>Blog DocFácil</h1>
            <p>Tips, guías y estrategias para médicos y dentistas que quieren hacer crecer su consultorio.</p>
        </div>

        <div class="grid">
            @foreach($articles as $slug => $a)
            <a href="/blog/{{ $slug }}" class="card">
                <div class="card-img">
                    @php
                    $emojis = ['Gestión' => '📊', 'Tecnología' => '💻', 'Legal' => '⚖️', 'Odontología' => '🦷'];
                    @endphp
                    {{ $emojis[$a['category']] ?? '📝' }}
                </div>
                <div class="card-body">
                    <div class="card-cat">{{ $a['category'] }}</div>
                    <div class="card-title">{{ $a['title'] }}</div>
                    <div class="card-desc">{{ $a['description'] }}</div>
                    <div class="card-meta">
                        <span>{{ \Carbon\Carbon::parse($a['date'])->translatedFormat('d M Y') }}</span>
                        <span>·</span>
                        <span>{{ $a['read_time'] }} lectura</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            &copy; {{ date('Y') }} DocFácil — <a href="/">Software para consultorios médicos y dentales</a>
        </div>
    </footer>
</body>
</html>
