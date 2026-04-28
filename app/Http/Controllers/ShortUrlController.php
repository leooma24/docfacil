<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;

class ShortUrlController extends Controller
{
    /**
     * Resuelve un código de URL corta y redirige al target.
     * Si está expirada o no existe, 404 sin filtrarse información.
     */
    public function redirect(Request $request, string $code)
    {
        $short = ShortUrl::where('code', $code)->first();

        if (!$short || $short->isExpired()) {
            abort(404);
        }

        // Incrementar contador (sin tocar updated_at para no flutter cache)
        $short->increment('clicks');

        return redirect()->away($short->target_url, 302);
    }
}
