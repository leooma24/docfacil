<x-filament-panels::page>
    {{-- Estilos en línea a propósito: en producción algunas utilidades de Tailwind v4 no compilan. --}}
    @if ($faltaba = $this->getFaltabaParaReceta())
        <div style="padding:12px 16px;border-radius:12px;background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:14px;">
            Para imprimir la receta falta {{ implode(' y ', $faltaba) }}. Complétalo aquí y vuelve a descargarla.
        </div>
    @endif

    <form wire:submit="guardar">
        {{ $this->form }}

        <div style="margin-top:16px;">
            <x-filament::button type="submit">Guardar</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
