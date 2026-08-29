{{-- Ayuda por WhatsApp, fija abajo a la derecha del panel doctor.

     Antes no habia a donde acudir: el asistente IA esta detras del kill
     switch y el menu de usuario solo tiene "Salir". Un dentista atorado a
     las 9 de la noche se quedaba sin salida.

     Va por wa.me al numero del consultorio (no la API), igual que el resto
     de los botones de WhatsApp del sistema. El mensaje incluye la pantalla
     donde estaba, para no empezar preguntando "¿en donde andabas?". --}}
@php
    $telefonoAyuda = '526682493398';
@endphp

<style>
    .df-ayuda {
        position: fixed; right: 20px; bottom: 20px; z-index: 40;
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 16px 10px 12px; border-radius: 999px;
        background: #ffffff; color: #0f172a;
        border: 1px solid #e2e8f0; box-shadow: 0 6px 20px rgba(15, 23, 42, 0.12);
        font: 600 0.82rem/1 'Inter', system-ui, sans-serif; text-decoration: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .df-ayuda:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(15, 23, 42, 0.18); }
    .df-ayuda svg { width: 18px; height: 18px; color: #22c55e; flex-shrink: 0; }
    .dark .df-ayuda { background: #1e293b; color: #e2e8f0; border-color: #334155; }

    /* En celular solo el icono: la pantalla ya va bastante apretada. */
    @media (max-width: 640px) {
        .df-ayuda { padding: 12px; right: 14px; bottom: 14px; }
        .df-ayuda span { display: none; }
    }

    /* No taparle el boton de guardar de los formularios largos. */
    @media (max-height: 520px) { .df-ayuda { display: none; } }
</style>

<a href="#" class="df-ayuda" target="_blank" rel="noopener"
   id="df-boton-ayuda" aria-label="Pedir ayuda por WhatsApp">
    <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884a9.82 9.82 0 016.988 2.9 9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/>
    </svg>
    <span>¿Te ayudo?</span>
</a>

<script>
    (function () {
        var boton = document.getElementById('df-boton-ayuda');
        if (!boton) return;

        // La liga se arma al hacer clic para que traiga la pantalla actual,
        // que en un panel Livewire cambia sin recargar la pagina.
        boton.addEventListener('click', function (e) {
            e.preventDefault();

            var pantalla = (document.querySelector('h1.fi-header-heading') || {}).textContent || document.title;
            var mensaje = 'Hola, soy usuario de DocFacil y necesito ayuda.\n\n'
                + 'Estoy en: ' + pantalla.trim();

            window.open(
                'https://wa.me/{{ $telefonoAyuda }}?text=' + encodeURIComponent(mensaje),
                '_blank',
                'noopener'
            );
        });
    })();
</script>
