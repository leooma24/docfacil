<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\SpeiPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Sirve los comprobantes SPEI (privados) solo a usuarios autorizados.
 * Los archivos viven en el disco 'local' (no expuesto públicamente) porque
 * contienen datos bancarios (CLABE, RFC, nombre del titular).
 */
class SpeiReceiptController extends Controller
{
    public function download(Request $request, SpeiPayment $payment): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        // Solo admins ven comprobantes. El cliente que lo subió puede verlo también.
        $isAdmin = ($user->role ?? null) === 'admin';
        $isOwner = (int) $payment->user_id === (int) $user->id;

        abort_unless($isAdmin || $isOwner, 403);

        $disk = Storage::disk('local');
        abort_unless($payment->receipt_path && $disk->exists($payment->receipt_path), 404);

        return $disk->download(
            $payment->receipt_path,
            $payment->receipt_original_name ?: 'comprobante-spei.' . pathinfo($payment->receipt_path, PATHINFO_EXTENSION),
            [
                'Content-Type' => $payment->receipt_mime ?: 'application/octet-stream',
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            ]
        );
    }
}
