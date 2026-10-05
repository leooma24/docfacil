<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Se recarga sola: la tele de la sala no tiene quien le dé clic. --}}
    <meta http-equiv="refresh" content="15">
    <meta name="robots" content="noindex, nofollow">
    <title>Sala de espera · {{ $clinica->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --fondo: #f0fdfa; --tarjeta: #ffffff; --texto: #0f172a; --suave: #475569;
            --marca: #0f766e; --marca-2: #0891b2; --borde: #ccfbf1; --llego: #059669; --llego-fondo: #d1fae5;
        }
        * { box-sizing: border-box; margin: 0; }
        html, body { height: 100%; }
        body { background: var(--fondo); color: var(--texto); font-family: Inter, system-ui, sans-serif; display: flex; flex-direction: column; padding: 3vh 4vw; gap: 3vh; }
        header { display: flex; align-items: center; justify-content: space-between; gap: 2vw; }
        .consultorio { font-size: clamp(22px, 3.4vw, 54px); font-weight: 800; color: var(--marca); line-height: 1.1; }
        .reloj { font-size: clamp(22px, 3.4vw, 54px); font-weight: 800; color: var(--suave); font-variant-numeric: tabular-nums; }
        main { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 3vw; min-height: 0; }
        section { background: var(--tarjeta); border: 2px solid var(--borde); border-radius: 28px; padding: 3vh 2.5vw; display: flex; flex-direction: column; gap: 2vh; min-height: 0; }
        h2 { font-size: clamp(18px, 2.2vw, 36px); font-weight: 700; color: var(--suave); text-transform: uppercase; letter-spacing: .06em; }
        .en-consulta .fila { background: linear-gradient(135deg, var(--marca), var(--marca-2)); color: #fff; border-radius: 20px; padding: 2.4vh 2vw; }
        .en-consulta .nombre { font-size: clamp(32px, 5vw, 88px); font-weight: 800; line-height: 1.05; }
        .en-consulta .doctor { font-size: clamp(16px, 1.8vw, 30px); font-weight: 500; opacity: .92; margin-top: .6vh; }
        .siguen .fila { display: flex; align-items: center; justify-content: space-between; gap: 1.5vw; padding: 1.6vh 0; border-bottom: 2px solid var(--borde); }
        .siguen .fila:last-child { border-bottom: 0; }
        .siguen .nombre { font-size: clamp(24px, 3.4vw, 60px); font-weight: 800; }
        .lado { display: flex; align-items: center; gap: 1.2vw; flex: none; }
        .hora { font-size: clamp(20px, 2.8vw, 48px); font-weight: 700; color: var(--suave); font-variant-numeric: tabular-nums; }
        .llego { font-size: clamp(14px, 1.5vw, 26px); font-weight: 700; color: var(--llego); background: var(--llego-fondo); border-radius: 999px; padding: .5vh 1vw; }
        .vacio { font-size: clamp(18px, 2.2vw, 36px); color: var(--suave); font-weight: 500; }
        footer { display: flex; align-items: center; justify-content: center; gap: .8vw; color: var(--suave); font-size: clamp(12px, 1.2vw, 20px); }
        footer img { height: clamp(16px, 1.6vw, 26px); }
        @media (max-width: 800px) {
            main { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <div class="consultorio">{{ $clinica->name }}</div>
        <div class="reloj" id="reloj">{{ now()->format('H:i') }}</div>
    </header>

    <main>
        <section class="en-consulta">
            <h2>En consulta</h2>
            @forelse($enConsulta as $cita)
                <div class="fila">
                    <div class="nombre">{{ $cita['nombre'] }}</div>
                    @if($cita['doctor'])
                        <div class="doctor">con {{ $cita['doctor'] }}</div>
                    @endif
                </div>
            @empty
                <div class="vacio">En un momento le llamamos.</div>
            @endforelse
        </section>

        <section class="siguen">
            <h2>Siguen</h2>
            @forelse($siguen as $cita)
                <div class="fila">
                    <div class="nombre">{{ $cita['nombre'] }}</div>
                    <div class="lado">
                        @if($cita['llego'])
                            <span class="llego">Ya llegó</span>
                        @endif
                        <span class="hora">{{ $cita['hora'] }}</span>
                    </div>
                </div>
            @empty
                <div class="vacio">No hay más citas por hoy.</div>
            @endforelse
        </section>
    </main>

    <footer>
        <img src="{{ asset('images/solo_logo.png') }}" alt="">
        <span>DocFácil</span>
    </footer>

    <script>
        // El reloj camina entre recargas con la hora del consultorio, no la
        // de la tele, que puede estar mal puesta.
        (function () {
            var el = document.getElementById('reloj');
            var base = {{ now()->hour * 60 + now()->minute }}, desde = Date.now();
            setInterval(function () {
                var m = (base + Math.floor((Date.now() - desde) / 60000)) % 1440;
                el.textContent = String(Math.floor(m / 60)).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0');
            }, 10000);
        })();
    </script>
</body>
</html>
