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
    $user = \App\Models\User::where('email', 'demo@docfacil.com')->first();
    if ($user) {
        auth()->login($user);
        return redirect('/doctor');
    }
    return redirect('/');
})->middleware('throttle:10,1')->name('demo');

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/software-dental/{city}', [CityLandingController::class, 'show']);

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/invitation/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
    Route::post('/invitation/{token}', [InvitationController::class, 'store'])->name('invitation.store');
});
