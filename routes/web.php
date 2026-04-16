<?php

use App\Http\Controllers\Billing\SpeiReceiptController;
use App\Http\Controllers\Billing\StripeCheckoutController;
use App\Http\Controllers\Billing\StripeWebhookController;
use App\Http\Controllers\BriefPdfController;
use App\Http\Controllers\BrochureController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CityLandingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DemoModeController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/privacidad', 'legal.privacidad')->name('legal.privacy');
Route::view('/terminos', 'legal.terminos')->name('legal.terms');

// Marketing: brief y brochure
Route::get('/brief.pdf', [BriefPdfController::class, 'download'])->name('brief.pdf');
Route::get('/brief', [BriefPdfController::class, 'web'])->name('brief.web');
Route::get('/brochure', [BrochureController::class, 'web'])->name('brochure.web');
Route::get('/brochure.pdf', [BrochureController::class, 'pdf'])->name('brochure.pdf');

// Billing: Stripe Checkout (autenticado) + webhook (sin CSRF) + comprobantes SPEI privados
Route::middleware(['auth'])->group(function () {
    Route::get('/billing/stripe/checkout/{plan}/{cycle}', [StripeCheckoutController::class, 'checkout'])
        ->name('stripe.checkout')
        ->where('plan', 'basico|profesional|clinica')
        ->where('cycle', 'monthly|annual');
    Route::get('/billing/stripe/success', [StripeCheckoutController::class, 'success'])
        ->name('stripe.checkout.success');

    // Descarga de comprobantes SPEI — auth + admin-or-owner check dentro del controller
    Route::get('/billing/spei-receipts/{payment}', [SpeiReceiptController::class, 'download'])
        ->name('spei.receipt.download');
});

Route::post('/billing/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

Route::post('/contacto', [ContactController::class, 'store'])
    ->name('contact.store')
    ->middleware('throttle:5,1');

Route::post('/chatbot/message', [ChatbotController::class, 'message'])
    ->name('chatbot.message')
    ->middleware('throttle:20,1');
Route::post('/chatbot/close', [ChatbotController::class, 'close'])
    ->name('chatbot.close')
    ->middleware('throttle:10,1');
Route::post('/chatbot/create-account', [ChatbotController::class, 'createAccount'])
    ->name('chatbot.createAccount')
    ->middleware('throttle:5,60');
Route::get('/chatbot/auto-login/{user}', [ChatbotController::class, 'autoLogin'])
    ->name('chatbot.autoLogin')
    ->middleware('signed');

Route::get('/demo', function () {
    session()->flash('demo_credentials', [
        'email' => 'demo@docfacil.com',
        'password' => 'demo2026',
    ]);
    return redirect('/doctor/login');
})->middleware('throttle:10,1')->name('demo');

Route::get('/beta', function () {
    return view('beta');
})->name('beta');

Route::post('/beta', function (\Illuminate\Http\Request $request) {
    if ($request->filled('website_url')) {
        return back()->with('beta_success', true);
    }

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:50',
        'clinic_name' => 'nullable|string|max:255',
        'specialty' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:255',
    ]);

    \App\Models\Prospect::updateOrCreate(
        ['email' => $request->email],
        [
            'name' => $request->name,
            'phone' => $request->phone,
            'clinic_name' => $request->clinic_name,
            'specialty' => $request->specialty,
            'city' => $request->city,
            'source' => 'landing',
            'status' => 'interested',
            'notes' => 'BETA TESTER - Registro desde /beta',
        ]
    );

    return back()->with('beta_success', true);
})->name('beta.store')->middleware('throttle:5,1');

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/software-dental/{city}', [CityLandingController::class, 'show']);
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');
Route::get('/sales/proposal/{prospect}/pdf', [\App\Http\Controllers\ProposalPdfController::class, '__invoke'])
    ->middleware('auth')->name('sales.proposal.pdf');

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/invitation/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
    Route::post('/invitation/{token}', [InvitationController::class, 'store'])->name('invitation.store');
});

// Demo mode for sales reps - creates a temporary clinic with fake data
Route::get('/demo-vendedor', [DemoModeController::class, 'start'])
    ->middleware('throttle:10,60')
    ->name('demo.vendedor');

// WhatsApp webhook (Meta will call these)
Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Public check-in for patients
Route::get('/clinica/{slug}/check-in', [CheckInController::class, 'show'])
    ->middleware('throttle:20,1')
    ->name('checkin.show');
Route::post('/clinica/{slug}/check-in', [CheckInController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('checkin.store');

Route::get('/doctor/receta/{prescription}/pdf', function (\App\Models\Prescription $prescription) {
    abort_unless(auth()->check() && auth()->user()->clinic_id === $prescription->clinic_id, 403);
    $prescription->load(['patient', 'doctor.user', 'doctor.clinic', 'items']);
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.prescription', ['prescription' => $prescription]);
    return $pdf->stream("receta-{$prescription->id}.pdf");
})->middleware('auth')->name('prescription.pdf');
