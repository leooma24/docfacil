<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Cómo van los mensajes</x-slot>
        <x-slot name="description">De cada mensaje que manda: cuántos lo recibieron y cuántos contestaron a ese. La respuesta cuenta para el último mensaje que recibió la persona antes de contestar.</x-slot>

        @if(empty($filas))
            <p style="font-size:14px;color:#64748b;">Todavía no hay mensajes registrados. Se cuentan los que abre desde la cola del día.</p>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="text-align:left;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.04em;">
                            <th style="padding:8px 6px;">Mensaje</th>
                            <th style="padding:8px 6px;text-align:right;">Lo recibieron</th>
                            <th style="padding:8px 6px;text-align:right;">Contestaron</th>
                            <th style="padding:8px 6px;text-align:right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filas as $fila)
                            <tr style="border-top:1px solid #e5e7eb;">
                                <td style="padding:10px 6px;">
                                    <div style="font-weight:600;color:#0f172a;">{{ $fila['etiqueta'] }}</div>
                                    @if($fila['enviados'] < \App\Filament\Sales\Widgets\ComoVanLosMensajesWidget::POCOS)
                                        <div style="font-size:12px;color:#b45309;">Todavía son pocos para comparar ({{ $fila['enviados'] }} de {{ \App\Filament\Sales\Widgets\ComoVanLosMensajesWidget::POCOS }}).</div>
                                    @endif
                                    @if($fila['estimado'])
                                        <div style="font-size:12px;color:#64748b;">Incluye envíos de antes del 5-oct, con fecha estimada.</div>
                                    @endif
                                </td>
                                <td style="padding:10px 6px;text-align:right;font-variant-numeric:tabular-nums;">{{ $fila['enviados'] }}</td>
                                <td style="padding:10px 6px;text-align:right;font-variant-numeric:tabular-nums;">{{ $fila['contestaron'] }}</td>
                                <td style="padding:10px 6px;text-align:right;font-weight:700;font-variant-numeric:tabular-nums;color:#0f766e;">{{ number_format($fila['porcentaje'], 0) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
