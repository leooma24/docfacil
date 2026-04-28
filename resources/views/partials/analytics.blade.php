{{-- Google Analytics 4 — solo se renderiza si ANALYTICS_ENABLED=true y
     hay un GA_MEASUREMENT_ID configurado. Diseñado para LFPDPPP / privacy:
     - IP anonimizada (anonymize_ip)
     - Sin tracking si el usuario tiene Do-Not-Track activo
     - Solo cookies _ga* (analytics, no marketing/ads)
     - Helper window.trackEvent() para disparar eventos custom desde Blade/JS
--}}
@php
    $analyticsEnabled = config('services.analytics.enabled')
        && config('services.analytics.ga_measurement_id');
@endphp

@if($analyticsEnabled)
    @php $gaId = config('services.analytics.ga_measurement_id'); @endphp
    <script>
        // Respetamos Do-Not-Track del navegador. Si DNT=1, no cargamos GA.
        if (navigator.doNotTrack === '1' || window.doNotTrack === '1') {
            window.trackEvent = function(){};
        } else {
            (function () {
                var s = document.createElement('script');
                s.async = true;
                s.src = 'https://www.googletagmanager.com/gtag/js?id={{ $gaId }}';
                document.head.appendChild(s);
            })();

            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }
            window.gtag = gtag;
            gtag('js', new Date());
            gtag('config', @json($gaId), {
                'anonymize_ip': true,
                'cookie_flags': 'SameSite=Lax;Secure',
                'send_page_view': true,
            });

            // Helper global para disparar eventos desde otros scripts/Blade.
            // Uso: trackEvent('cta_clicked', { location: 'hero', text: 'Probar 15 dias' })
            window.trackEvent = function (eventName, params) {
                try {
                    gtag('event', eventName, params || {});
                } catch (e) {
                    if (window.console) console.warn('trackEvent failed:', e);
                }
            };

            // Auto-tracker: cualquier elemento con data-track="evento_name" dispara
            // el evento en click. Las propiedades vienen de data-track-* (camelCase
            // en JS). Ej: <a data-track="cta_clicked" data-track-location="hero">
            document.addEventListener('click', function (e) {
                var el = e.target.closest('[data-track]');
                if (!el) return;
                var eventName = el.getAttribute('data-track');
                if (!eventName) return;
                var props = {};
                Array.from(el.attributes).forEach(function (a) {
                    if (a.name.startsWith('data-track-') && a.name !== 'data-track') {
                        var key = a.name.replace('data-track-', '').replace(/-/g, '_');
                        props[key] = a.value;
                    }
                });
                window.trackEvent(eventName, props);
            });

            // Eventos flasheados desde server-side: cualquier controlador puede
            // hacer `session()->push('analytics_events', ['name'=>..., 'params'=>...])`
            // y se dispararan aqui en el siguiente pageload.
            @php $flashedEvents = session()->pull('analytics_events', []); @endphp
            @if(!empty($flashedEvents) && is_array($flashedEvents))
                @foreach($flashedEvents as $ev)
                    @if(is_array($ev) && !empty($ev['name']))
                        window.trackEvent(@json($ev['name']), @json($ev['params'] ?? []));
                    @endif
                @endforeach
            @endif
        }
    </script>
@endif
