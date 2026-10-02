{{-- Un video de venta en la página. Se reproduce solo cuando el doctor le da
     play, y con sonido: la voz es el gancho. preload="none" para que el que
     llega desde WhatsApp en su celular no baje nada hasta que toque. --}}
<figure class="lp-video">
    <video controls playsinline preload="none"
           poster="{{ asset('videos/' . $id . '-poster.jpg') }}"
           width="720" height="900"
           aria-label="{{ $v['titulo'] }}"
           onplay="window.trackEvent && window.trackEvent('video_played', { video: '{{ $id }}' })">
        <source src="{{ asset('videos/' . $id . '.mp4') }}" type="video/mp4">
    </video>
    <figcaption>{{ $v['titulo'] }} · {{ $v['dur'] }}</figcaption>
</figure>
