<?php

use App\Http\Controllers\CityLandingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/contacto', [ContactController::class, 'store'])
    ->name('contact.store')
    ->middleware('throttle:5,1');

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

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/invitation/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
    Route::post('/invitation/{token}', [InvitationController::class, 'store'])->name('invitation.store');
});
