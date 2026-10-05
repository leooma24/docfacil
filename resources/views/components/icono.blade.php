{{--
    Un ícono del panel, en lugar de un emoji: se ve igual en cualquier
    celular o computadora y toma el tamaño y el color del texto de junto.
    Uso: <x-icono nombre="calendar-days" />   (íconos de heroicons, contorno)
--}}
@props(['nombre'])
@svg('heroicon-o-' . $nombre, ['style' => 'width:1.1em;height:1.1em;display:inline-block;vertical-align:-0.2em;flex:none;', 'aria-hidden' => 'true'])
