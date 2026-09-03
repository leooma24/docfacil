@php
    $n = $this->getNumeros();
    $pesos = fn ($v) => '$' . number_format((float) $v, 2);

    // Estilos en linea a proposito: en produccion algunas utilidades de
    // Tailwind v4 no compilan, y este bloque es justo lo que el doctor viene
    // a ver. Ver CLAUDE.md.
    $tarjeta = 'border-radius:1rem;padding:1.25rem 1.5rem;border:1px solid rgba(0,0,0,.06);background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.06);';
    $etiqueta = 'font-size:.75rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;opacity:.6;';
    $cifra = 'font-size:1.875rem;font-weight:800;line-height:1.15;margin-top:.35rem;';
@endphp

<x-filament-panels::page>

    {{-- Hero --}}
    <div style="border-radius:1.25rem;padding:1.75rem 2rem;color:#fff;background:linear-gradient(135deg,#0f766e 0%,#0891b2 45%,#0ea5e9 100%);box-shadow:0 10px 30px rgba(8,145,178,.25);">
        <div style="font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.85;">📊 Cuánto te quedó</div>
        <div style="font-size:1.75rem;font-weight:800;margin-top:.25rem;">Corte del consultorio</div>
        <div style="opacity:.92;margin-top:.4rem;max-width:46rem;line-height:1.5;">
            Lo que entró, lo que salió y lo que te quedó. No es contabilidad para el SAT — eso lo hace tu contador.
            Esto es para que sepas cómo te fue sin sacar la calculadora.
        </div>
    </div>

    {{-- Periodo --}}
    <div style="{{ $tarjeta }}">
        {{ $this->form }}
        <div style="margin-top:.75rem;font-size:.8rem;opacity:.6;">
            Del {{ $n['desde']->translatedFormat('j \d\e F \d\e Y') }}
            al {{ $n['hasta']->translatedFormat('j \d\e F \d\e Y') }}
        </div>
    </div>

    @if (! $n['hay_datos'])

        <div style="{{ $tarjeta }} text-align:center;padding:3rem 1.5rem;">
            <div style="font-size:3rem;">📊</div>
            <div style="font-size:1.15rem;font-weight:700;margin-top:.5rem;">Todavía no hay nada que cortar</div>
            <p style="opacity:.65;margin-top:.5rem;max-width:32rem;margin-left:auto;margin-right:auto;line-height:1.55;">
                En cuanto registres cobros y gastos de este periodo, aquí te va a aparecer cuánto entró,
                cuánto salió y con cuánto te quedaste.
            </p>
            <div style="margin-top:1.25rem;display:flex;gap:.6rem;justify-content:center;flex-wrap:wrap;">
                <a href="{{ \App\Filament\Doctor\Resources\ExpenseResource::getUrl('create') }}"
                   style="display:inline-block;padding:.6rem 1.1rem;border-radius:.6rem;background:#d97706;color:#fff;font-weight:600;text-decoration:none;">
                    Registrar un gasto
                </a>
                <a href="{{ \App\Filament\Doctor\Resources\PaymentResource::getUrl('create') }}"
                   style="display:inline-block;padding:.6rem 1.1rem;border-radius:.6rem;background:#0d9488;color:#fff;font-weight:600;text-decoration:none;">
                    Registrar un cobro
                </a>
            </div>
        </div>

    @else

        {{-- Los tres números --}}
        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(15rem,1fr));">

            <div style="{{ $tarjeta }} border-left:5px solid #0d9488;">
                <div style="{{ $etiqueta }}">Entró</div>
                <div style="{{ $cifra }} color:#0d9488;">{{ $pesos($n['ingresos']) }}</div>
                @if ($n['cambio_ingresos'] !== null)
                    <div style="font-size:.8rem;margin-top:.4rem;opacity:.7;">
                        {{ $n['cambio_ingresos'] >= 0 ? '▲' : '▼' }}
                        {{ number_format(abs($n['cambio_ingresos']), 1) }}% vs. el periodo anterior
                    </div>
                @endif
            </div>

            <div style="{{ $tarjeta }} border-left:5px solid #d97706;">
                <div style="{{ $etiqueta }}">Salió</div>
                <div style="{{ $cifra }} color:#d97706;">{{ $pesos($n['gastos']) }}</div>
                @if ($n['cambio_gastos'] !== null)
                    <div style="font-size:.8rem;margin-top:.4rem;opacity:.7;">
                        {{ $n['cambio_gastos'] >= 0 ? '▲' : '▼' }}
                        {{ number_format(abs($n['cambio_gastos']), 1) }}% vs. el periodo anterior
                    </div>
                @endif
            </div>

            <div style="{{ $tarjeta }} border-left:5px solid {{ $n['utilidad'] >= 0 ? '#059669' : '#dc2626' }};">
                <div style="{{ $etiqueta }}">Te quedó</div>
                <div style="{{ $cifra }} color:{{ $n['utilidad'] >= 0 ? '#059669' : '#dc2626' }};">
                    {{ $pesos($n['utilidad']) }}
                </div>
                <div style="font-size:.8rem;margin-top:.4rem;opacity:.75;">
                    @if ($n['margen'] !== null)
                        De cada $100, te quedaron <strong>${{ number_format($n['margen'], 0) }}</strong>
                    @else
                        Sin ingresos en el periodo
                    @endif
                </div>
            </div>

        </div>

        @if ($n['utilidad'] < 0)
            <div style="{{ $tarjeta }} border-left:5px solid #dc2626;background:#fef2f2;">
                <strong style="color:#991b1b;">Este periodo gastaste más de lo que cobraste.</strong>
                <span style="opacity:.8;">
                    Puede ser normal si compraste equipo o pagaste algo grande de una vez.
                    Revisa abajo qué categoría se llevó más.
                </span>
            </div>
        @endif

        {{-- Por cobrar: va aparte, no es ingreso --}}
        @if ($n['por_cobrar'] > 0)
            <div style="{{ $tarjeta }} border-left:5px solid #6366f1;">
                <div style="{{ $etiqueta }}">Además, te deben</div>
                <div style="font-size:1.4rem;font-weight:800;margin-top:.3rem;color:#4f46e5;">
                    {{ $pesos($n['por_cobrar']) }}
                </div>
                <div style="font-size:.85rem;margin-top:.4rem;opacity:.7;line-height:1.5;">
                    Esto <strong>no</strong> está contado arriba, porque todavía no entra a la caja.
                    <a href="{{ \App\Filament\Doctor\Resources\PaymentResource::getUrl() }}" style="color:#4f46e5;font-weight:600;">Ver quién debe →</a>
                </div>
            </div>
        @endif

        {{-- En qué se fue --}}
        @if (! empty($n['categorias']))
            @php $mayor = max($n['categorias']); @endphp

            <div style="{{ $tarjeta }}">
                <div style="font-size:1.05rem;font-weight:700;">En qué se te fue</div>
                <div style="font-size:.85rem;opacity:.6;margin-top:.15rem;margin-bottom:1rem;">
                    De mayor a menor. Aquí es donde se ve qué vale la pena negociar.
                </div>

                @foreach ($n['categorias'] as $categoria => $monto)
                    @php
                        $porcentaje = $n['gastos'] > 0 ? ($monto / $n['gastos']) * 100 : 0;
                        $ancho = $mayor > 0 ? ($monto / $mayor) * 100 : 0;
                    @endphp
                    <div style="margin-bottom:.85rem;">
                        <div style="display:flex;justify-content:space-between;gap:1rem;font-size:.875rem;margin-bottom:.3rem;">
                            <span style="font-weight:600;">{{ $categoria }}</span>
                            <span style="white-space:nowrap;">
                                {{ $pesos($monto) }}
                                <span style="opacity:.55;">· {{ number_format($porcentaje, 0) }}%</span>
                            </span>
                        </div>
                        <div style="height:.55rem;border-radius:999px;background:rgba(0,0,0,.07);overflow:hidden;">
                            <div style="height:100%;width:{{ number_format($ancho, 2) }}%;border-radius:999px;background:linear-gradient(90deg,#d97706,#f59e0b);"></div>
                        </div>
                    </div>
                @endforeach

                <div style="margin-top:1.1rem;padding-top:.9rem;border-top:1px solid rgba(0,0,0,.07);display:flex;justify-content:space-between;font-weight:700;">
                    <span>Total de gastos</span>
                    <span>{{ $pesos($n['gastos']) }}</span>
                </div>
            </div>
        @endif

        {{-- Contra el periodo pasado --}}
        <div style="{{ $tarjeta }}">
            <div style="font-size:1.05rem;font-weight:700;margin-bottom:.85rem;">Contra el periodo anterior</div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
                    <thead>
                        <tr style="text-align:left;opacity:.6;font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">
                            <th style="padding:.5rem .25rem;"></th>
                            <th style="padding:.5rem .25rem;text-align:right;">Anterior</th>
                            <th style="padding:.5rem .25rem;text-align:right;">Este periodo</th>
                            <th style="padding:.5rem .25rem;text-align:right;">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            ['Entró', $n['ingresos_antes'], $n['ingresos']],
                            ['Salió', $n['gastos_antes'], $n['gastos']],
                            ['Te quedó', $n['utilidad_antes'], $n['utilidad']],
                        ] as [$fila, $antes, $ahora])
                            @php $dif = $ahora - $antes; @endphp
                            <tr style="border-top:1px solid rgba(0,0,0,.06);">
                                <td style="padding:.6rem .25rem;font-weight:600;">{{ $fila }}</td>
                                <td style="padding:.6rem .25rem;text-align:right;opacity:.7;">{{ $pesos($antes) }}</td>
                                <td style="padding:.6rem .25rem;text-align:right;font-weight:600;">{{ $pesos($ahora) }}</td>
                                <td style="padding:.6rem .25rem;text-align:right;color:{{ $dif >= 0 ? '#059669' : '#dc2626' }};">
                                    {{ $dif >= 0 ? '+' : '−' }}{{ $pesos(abs($dif)) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @endif

</x-filament-panels::page>
