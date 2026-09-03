<?php

/**
 * El programa Fundador.
 *
 * La escasez de aquí no es tiempo, son lugares: no se le puede dar acceso
 * directo al que construye el producto a quinientas personas. Por eso el
 * número que se enseña en la landing sale de contar los fundadores reales
 * en la base — no puede mentir, y no se reinicia como lo hacía el reloj de
 * "oferta de lanzamiento".
 */
return [
    // Cuántos lugares hay en total. Al llenarse, la landing deja de ofrecer
    // el programa y enseña el ahorro anual normal.
    'seats' => (int) env('FOUNDER_SEATS', 10),

    // Meses sin costo.
    'free_months' => (int) env('FOUNDER_FREE_MONTHS', 6),

    // Lo que paga de por vida después, congelado. Debe coincidir con el que
    // pone ClinicResource al marcar la clínica como fundadora.
    'monthly_price' => (float) env('FOUNDER_PRICE', 499),
];
