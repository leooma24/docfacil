<!doctype html>
<html lang="es-MX">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Anotado · DocFácil</title>
<style>body{font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;background:#f4f6f8;color:#14212b}
.c{background:#fff;padding:28px 32px;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);max-width:420px;text-align:center}
h1{font-size:20px;margin:0 0 8px}p{margin:6px 0;color:#4b5b68}</style></head>
<body><div class="c">
  <h1>{{ $nuevo ? '✓ Anotado en DocFácil' : 'Ya estaba anotado' }}</h1>
  <p>{{ $prospecto->name }}{{ $prospecto->clinic_name ? ' · '.$prospecto->clinic_name : '' }}</p>
  <p>{{ $nuevo ? 'Se registró el envío y su siguiente mensaje toca '.($prospecto->next_contact_at ? $prospecto->next_contact_at->locale('es')->isoFormat('dddd D [de] MMMM') : 'cuando termine la cadencia').'.' : 'Ya se había registrado hace menos de 5 minutos.' }}</p>
  <p>Puedes cerrar esta pestaña.</p>
</div><script>setTimeout(function(){ try { window.close(); } catch (e) {} }, 2500);</script></body></html>
