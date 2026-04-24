<?php

namespace App\Http\Controllers;

/**
 * Herramientas gratis publicas (engineering-as-marketing).
 *
 * Cada URL es un activo SEO permanente: Google las indexa, traen
 * trafico organico, convierten visitantes a prospectos sin gasto en
 * ads. Sin login, sin captura obligatoria — el doctor usa la herramienta
 * y el CTA lo lleva al landing si le gusto.
 */
class ToolsController extends Controller
{
    public function calculadoraRoi()
    {
        return view('tools.calculadora-roi');
    }
}
