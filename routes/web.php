<?php

use App\Http\Controllers\Billing\PremiumServiceCheckoutController;
use App\Http\Controllers\Billing\SpeiReceiptController;
use App\Http\Controllers\Billing\StripeCheckoutController;
use App\Http\Controllers\Billing\StripeWebhookController;
use App\Http\Controllers\AppointmentConfirmationController;
use App\Http\Controllers\Ventas\LigaDelTableroController;
use App\Http\Controllers\EstrenarCuentaController;
use App\Http\Controllers\ExportarDatosController;
use App\Http\Controllers\PatientPortalActivationController;
use App\Http\Controllers\BriefPdfController;
use App\Http\Controllers\BrochureController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\Cie10SearchController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DemoModeController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\UnsubscribeController;
use App\Http\Controllers\TreatmentPlanController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

// Raiz y /dentistas comparten la misma vista. La estrategia es 100% dental
// hasta llegar a 100 clinicas pagando — no tiene sentido mantener 2 landings
// que digan cosas distintas. /dentistas se mantiene para Google Ads /
// tracking diferenciado de email vs SEO. La vieja welcome.blade.php (la
// landing para médicos) se borró el 5-oct-2026: Omar decidió vender solo a
// dentistas y traía afirmaciones falsas. Queda en el historial de git.
Route::view('/', 'dentistas')->name('landing.home');
Route::view('/dentistas', 'dentistas')->name('landing.dentistas');

Route::view('/privacidad', 'legal.privacidad')->name('legal.privacy');
Route::view('/terminos', 'legal.terminos')->name('legal.terms');

// Marketing: brief y brochure
Route::get('/brief.pdf', [BriefPdfController::class, 'download'])->name('brief.pdf');
Route::get('/brief', [BriefPdfController::class, 'web'])->name('brief.web');
Route::get('/brochure', [BrochureController::class, 'web'])->name('brochure.web');
Route::get('/brochure.pdf', [BrochureController::class, 'pdf'])->name('brochure.pdf');

// Billing: Stripe Checkout (autenticado) + webhook (sin CSRF) + comprobantes SPEI privados
Route::middleware(['auth'])->group(function () {
    Route::get('/doctor/sus-datos/bajar', ExportarDatosController::class)
        ->middleware('throttle:5,60')
        ->name('datos.exportar');

    // CIE-10 catalog para autocompletado en la consulta médica (no-dental).
    // Rate limit: 60 búsquedas por minuto por usuario — suficiente para tipear
    // pero corta DoS si un user logueado intenta abusar.
    Route::get('/api/cie10/search', [Cie10SearchController::class, 'search'])
        ->middleware('throttle:60,1')
        ->name('cie10.search');
    Route::get('/api/cie10/resolve', [Cie10SearchController::class, 'resolve'])
        ->middleware('throttle:60,1')
        ->name('cie10.resolve');

    Route::get('/billing/stripe/checkout/{plan}/{cycle}', [StripeCheckoutController::class, 'checkout'])
        ->name('stripe.checkout')
        ->where('plan', 'basico|profesional|clinica')
        ->where('cycle', 'monthly|annual');
    Route::get('/billing/stripe/success', [StripeCheckoutController::class, 'success'])
        ->name('stripe.checkout.success');

    // Descarga de comprobantes SPEI — auth + admin-or-owner check dentro del controller
    Route::get('/billing/spei-receipts/{payment}', [SpeiReceiptController::class, 'download'])
        ->name('spei.receipt.download');

    // Marketplace de servicios premium (compra de addons)
    Route::get('/billing/premium/{purchase}/stripe', [PremiumServiceCheckoutController::class, 'stripe'])
        ->name('premium.checkout.stripe');
    Route::get('/billing/premium/{purchase}/spei', [PremiumServiceCheckoutController::class, 'spei'])
        ->name('premium.checkout.spei');
    Route::get('/billing/premium/{purchase}/quote', [PremiumServiceCheckoutController::class, 'quote'])
        ->name('premium.checkout.quote');
    Route::get('/billing/premium/{purchase}/success', [PremiumServiceCheckoutController::class, 'success'])
        ->name('premium.checkout.success');
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
Route::get('/chatbot/auto-login/{token}', [ChatbotController::class, 'autoLogin'])
    ->name('chatbot.autoLogin')
    ->where('token', '[a-f0-9]{64}')
    ->middleware('throttle:10,1');

Route::get('/demo', function () {
    session()->flash('demo_credentials', [
        'email' => 'demo@docfacil.com',
        'password' => 'demo2026',
    ]);
    return redirect('/doctor/login');
})->middleware('throttle:10,1')->name('demo');

// Programa beta retirado (2026-04-24): contradecia el pricing de planes pagados.
// Links legacy van al landing dental con redirect permanente (301) para preservar SEO.
// Las clinicas historicas con is_beta=true conservan su acceso via upgrade.blade.php.
Route::redirect('/beta', '/dentistas', 301)->name('beta');
Route::post('/beta', fn () => redirect('/dentistas', 301))->name('beta.store');

Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// Herramientas gratis publicas (engineering-as-marketing, SEO)
Route::get('/herramientas/calculadora-consultorio', [ToolsController::class, 'calculadoraRoi'])
    ->name('tools.calculadora_roi');

// Tracking de clicks en correos del pipeline de prospects
Route::get('/t/c/{token}', [TrackController::class, 'click'])
    ->name('track.click')
    ->middleware('throttle:60,1');

// One-click unsubscribe (LFPDPPP art. 16, anti-spam best practice).
// Token HMAC-SHA256 firmado por prospect_id; cualquier modificacion lo invalida.
Route::get('/baja/{token}', [UnsubscribeController::class, 'handle'])
    ->name('prospect.unsubscribe')
    ->middleware('throttle:30,1');

// URL corta para reemplazar links firmados largos en WhatsApp.
// Código de 6 chars resuelve a la URL real. ~30 chars vs ~250.
//
// Vive en /s/ y no en /c/ porque ahí chocaba con la confirmación de cita:
// las dos rutas competían por la misma dirección y ganaba esta, así que en
// cuanto las citas llegaran a seis dígitos el recordatorio iba a mandar al
// paciente a un 404. No se rompe nada al moverla: no había ninguna creada.
Route::get('/s/{code}', [ShortUrlController::class, 'redirect'])
    ->name('shortlink')
    ->where('code', '[A-Za-z0-9]{6,12}')
    ->middleware('throttle:120,1');
Route::post('/herramientas/calculadora-consultorio/lead', [ToolsController::class, 'calculadoraRoiLead'])
    ->middleware('throttle:5,1')
    ->name('tools.calculadora_roi.lead');
// Las páginas por ciudad se retiraron (12-oct-2026): no traían a ningún
// dentista y 40 copias del mismo texto son páginas puerta para Google. Las
// ligas viejas llevan al inicio.
Route::get('/software-dental/{city}', fn () => redirect()->route('landing.home', status: 301));

// Páginas de comparativa vs competidores (alta intención SEO + AI-SEO)
Route::get('/vs/{competitor}', [\App\Http\Controllers\ComparisonController::class, 'versus'])
    ->name('comparison.versus');
Route::get('/alternativas-a-{competitor}', [\App\Http\Controllers\ComparisonController::class, 'alternatives'])
    ->name('comparison.alternatives');
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');
Route::get('/sales/proposal/{prospect}/pdf', [\App\Http\Controllers\ProposalPdfController::class, '__invoke'])
    ->middleware('auth')->name('sales.proposal.pdf');

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/invitation/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
    Route::post('/invitation/{token}', [InvitationController::class, 'store'])->name('invitation.store');
});

// El paciente elige su contraseña para entrar al portal. La liga la manda su
// consultorio por WhatsApp y va firmada (caduca a los 7 dias), asi que no
// guardamos tokens en ninguna tabla.
Route::middleware(['signed', 'throttle:10,1'])->group(function () {
    Route::get('/paciente/activar/{patient}', [PatientPortalActivationController::class, 'show'])
        ->name('paciente.activar');
    Route::post('/paciente/activar/{patient}', [PatientPortalActivationController::class, 'store'])
        ->name('paciente.activar.store');
});

// El doctor elige su contrasena para estrenar su cuenta, cuando se le dejo el
// consultorio armado en vez de que se registrara el. Dura 7 dias: la abre
// cuando sale de consulta, no en el minuto en que se genero. La de "olvide mi
// contrasena" sigue durando 60 minutos, que para eso esta bien.
Route::middleware(['signed', 'throttle:10,1'])->group(function () {
    Route::get('/doctor/estrenar/{user}', [EstrenarCuentaController::class, 'show'])
        ->name('doctor.estrenar');
    Route::post('/doctor/estrenar/{user}', [EstrenarCuentaController::class, 'store'])
        ->name('doctor.estrenar.store');
});

// Demo para vendedores: crea un consultorio temporal con datos falsos y deja
// la sesion iniciada. Estaba abierta a internet, y cada visita sembraba ~180
// registros en la base y regalaba una sesion de doctor a un desconocido.
// Ahora pide la llave de DEMO_VENDEDOR_TOKEN; sin esa variable queda apagada.
Route::get('/demo-vendedor', function (\Illuminate\Http\Request $request, DemoModeController $demo) {
    $llave = config('services.demo_vendedor_token');

    abort_unless($llave && hash_equals($llave, (string) $request->query('k')), 404);

    return $demo->start();
})
    ->middleware('throttle:10,60')
    ->name('demo.vendedor');

// WhatsApp webhook (Meta will call these)
Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Confirmacion de cita 1-clic desde WhatsApp (ruta firmada, sin auth)
Route::get('/c/{appointment}', [AppointmentConfirmationController::class, 'show'])
    ->whereNumber('appointment')
    ->middleware(['signed', 'throttle:30,1'])
    ->name('appointment.confirm');

// "Ya no quiero recibirlo" desde el correo del corte del mes. Va firmada:
// sin firma, cualquiera podría apagarle el correo a otro consultorio.
Route::get('/corte/sin-correo/{clinic}', \App\Http\Controllers\CorteSinCorreoController::class)
    ->middleware(['signed', 'throttle:10,1'])
    ->name('corte.sin-correo');

// Check-in del paciente en la sala de espera. Va firmado: la liga sale del QR
// que imprime el consultorio y no se puede adivinar con el nombre del negocio.
// Sin la firma, cualquiera podia preguntar por un telefono y la pantalla le
// decia si esa persona era paciente de ese consultorio.
Route::get('/clinica/{slug}/check-in', [CheckInController::class, 'show'])
    ->middleware(['signed', 'throttle:20,1'])
    ->name('checkin.show');
Route::post('/clinica/{slug}/check-in', [CheckInController::class, 'store'])
    ->middleware(['signed', 'throttle:5,1'])
    ->name('checkin.store');

// La pantalla de la sala de espera (quién está en consulta y quién sigue).
// Firmada, como el QR: sin la firma, cualquiera vería quién tiene cita hoy.
Route::get('/clinica/{slug}/sala', \App\Http\Controllers\SalaDeEsperaController::class)
    ->middleware(['signed', 'throttle:30,1'])
    ->name('sala.pantalla');

// Aviso de privacidad del consultorio para sus pacientes, y la liga firmada
// para que el paciente lo acepte desde su celular (ley de datos, arts. 8 y 16).
Route::get('/clinica/{slug}/aviso-de-privacidad', [\App\Http\Controllers\AvisoDePrivacidadController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('aviso-privacidad.show');
Route::middleware(['signed', 'throttle:10,1'])->group(function () {
    Route::get('/clinica/{slug}/aviso-de-privacidad/aceptar/{paciente}', [\App\Http\Controllers\AvisoDePrivacidadController::class, 'formulario'])
        ->whereNumber('paciente')
        ->name('aviso-privacidad.formulario');
    Route::post('/clinica/{slug}/aviso-de-privacidad/aceptar/{paciente}', [\App\Http\Controllers\AvisoDePrivacidadController::class, 'aceptar'])
        ->whereNumber('paciente')
        ->name('aviso-privacidad.aceptar');
});

// Public booking portal (Pro+): solicitud de cita sin auth, feature-gated
// Horarios libres del consultorio, para que el paciente elija de una lista
// en vez de escribir una hora a ciegas.
Route::get('/clinica/{slug}/horarios-libres', [PublicBookingController::class, 'horariosLibres'])
    ->middleware('throttle:60,1')
    ->name('public-booking.horarios');

Route::get('/clinica/{slug}/agendar', [PublicBookingController::class, 'show'])
    ->middleware('throttle:20,1')
    ->name('public.booking.show');
Route::post('/clinica/{slug}/agendar', [PublicBookingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('public.booking.store');

// Planes de tratamiento / Presupuestos: PDF del doctor (auth) + vista publica (token)
Route::get('/doctor/presupuestos/{treatmentPlan}/pdf', [TreatmentPlanController::class, 'downloadPdf'])
    ->middleware('auth')->name('treatment-plan.pdf');
Route::get('/p/{token}', [TreatmentPlanController::class, 'publicShow'])
    ->middleware('throttle:30,1')
    ->where('token', '[a-f0-9]{64}')
    ->name('treatment-plan.public');
// Abrir la liga no acepta ni rechaza: lleva al presupuesto. Lo hace el
// botón (POST), que el paciente tiene que tocar. La vista previa de WhatsApp
// o un antivirus abren las ligas solos (auditoría del 12-oct-2026). Las ligas
// viejas que ya se mandaron siguen sirviendo para ver el presupuesto.
Route::get('/p/{token}/aceptar', [TreatmentPlanController::class, 'aVerlo'])
    ->middleware('throttle:10,1')
    ->where('token', '[a-f0-9]{64}')
    ->name('treatment-plan.accept');
Route::post('/p/{token}/aceptar', [TreatmentPlanController::class, 'accept'])
    ->middleware('throttle:10,1')
    ->where('token', '[a-f0-9]{64}');
Route::get('/p/{token}/rechazar', [TreatmentPlanController::class, 'aVerlo'])
    ->middleware('throttle:10,1')
    ->where('token', '[a-f0-9]{64}')
    ->name('treatment-plan.reject');
Route::post('/p/{token}/rechazar', [TreatmentPlanController::class, 'reject'])
    ->middleware('throttle:10,1')
    ->where('token', '[a-f0-9]{64}');

// El botón de WhatsApp del recordatorio pasa por aquí: deja recordadas todas
// las citas del paciente de ese día y abre WhatsApp con un solo mensaje. Así
// DocFácil sabe a quién ya se le escribió.
Route::get('/doctor/citas/{appointment}/recordar', function (int $appointment) {
    abort_unless(auth()->check(), 403);

    $cita = \App\Models\Appointment::with(['patient', 'clinic'])
        ->where('clinic_id', auth()->user()->clinic_id)
        ->findOrFail($appointment);

    $whatsapp = \App\Support\RecordatorioDeCita::ligaDeWhatsapp($cita);
    abort_unless($whatsapp, 422, 'El paciente no tiene teléfono.');

    \App\Models\Appointment::whereIn('id', \App\Support\RecordatorioDeCita::delMismoDia($cita)->pluck('id'))
        ->update(['reminder_sent' => true, 'reminder_sent_at' => now()]);

    return redirect()->away($whatsapp);
})->name('cita.recordar');

// Un archivo del expediente (foto de la hoja vieja, radiografía, PDF). Viven en
// el disco privado: solo se abren con sesión y desde su consultorio.
Route::get('/doctor/archivos/{archivo}', function (int $archivo) {
    abort_unless(auth()->check(), 403);

    $file = \App\Models\PatientFile::where('clinic_id', auth()->user()->clinic_id)->findOrFail($archivo);
    abort_unless(\Illuminate\Support\Facades\Storage::disk('local')->exists($file->path), 404);

    return \Illuminate\Support\Facades\Storage::disk('local')->response($file->path, $file->nombre, [
        'Content-Type' => $file->mime,
        'Cache-Control' => 'private, no-store',
    ]);
})->name('paciente.archivo');

// La agenda en papel, por si se va el internet: la de mañana por default,
// con teléfono, alertas y lo que debe cada paciente. Solo su consultorio.
Route::get('/doctor/agenda/imprimir', function () {
    abort_unless(auth()->check(), 403);
    $clinicId = auth()->user()->clinic_id;

    $pedido = request()->query('dia');
    $dia = is_string($pedido) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $pedido)
        ? rescue(fn () => \Carbon\Carbon::createFromFormat('!Y-m-d', $pedido), today()->addDay(), false)
        : today()->addDay();

    $citas = \App\Models\Appointment::with(['patient', 'service', 'doctor.user'])
        ->where('clinic_id', $clinicId)
        ->whereDate('starts_at', $dia->toDateString())
        ->whereNotIn('status', ['cancelled'])
        ->orderBy('starts_at')
        ->get();

    $deudas = \App\Models\Payment::where('clinic_id', $clinicId)
        ->whereIn('patient_id', $citas->pluck('patient_id')->unique())
        ->withBalance()->yaToca()
        ->selectRaw('patient_id, SUM(amount - amount_paid) as saldo')
        ->groupBy('patient_id')->pluck('saldo', 'patient_id');

    return view('agenda.imprimir', [
        'clinica' => auth()->user()->clinic,
        'dia' => $dia,
        'citas' => $citas,
        'deudas' => $deudas,
    ]);
})->name('agenda.imprimir');

// El recibo de un cobro: lo que cuesta, cada abono y lo que falta. Solo del
// consultorio de quien entra. Es un comprobante, no una factura (no hay CFDI).
Route::get('/doctor/cobros/{payment}/recibo', function (int $payment) {
    abort_unless(auth()->check(), 403);

    $cobro = \App\Models\Payment::where('clinic_id', auth()->user()->clinic_id)->findOrFail($payment);
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.recibo', \App\Support\CajaDelDia::datosDelRecibo($cobro));

    return $pdf->stream("recibo-{$cobro->id}.pdf");
})->name('cobro.recibo');

// Recordarle a un paciente su presupuesto pendiente: marca la fecha (un
// recordatorio al mes, no más) y abre WhatsApp con el mensaje escrito. Nada sale
// solo: el doctor da enviar desde su propio WhatsApp.
Route::get('/doctor/presupuestos/{plan}/recordar', function (int $plan) {
    abort_unless(auth()->check(), 403);

    $presupuesto = \App\Models\TreatmentPlan::with(['patient', 'clinic'])
        ->where('clinic_id', auth()->user()->clinic_id)
        ->findOrFail($plan);

    $telefono = preg_replace('/\D/', '', (string) $presupuesto->patient?->telefonoDeContacto());
    abort_if($telefono === '', 422, 'El paciente no tiene teléfono.');
    if (strlen($telefono) === 10) {
        $telefono = '52' . $telefono;
    }

    if (empty($presupuesto->public_token)) {
        $presupuesto->generatePublicToken();
    }

    $nombre = $presupuesto->patient->nombreDeContacto() ?: 'Hola';
    // Si el mensaje le llega a su mamá, se dice de quién es el plan.
    $deQuien = $presupuesto->patient->responsable ? ' de ' . trim((string) $presupuesto->patient->first_name) : '';
    $consultorio = $presupuesto->clinic?->name ?? 'su consultorio';
    $liga = route('treatment-plan.public', ['token' => $presupuesto->public_token]);

    // Los de usted y sin presión: es un recordatorio, no un empujón.
    $mensaje = $presupuesto->status === 'accepted'
        ? "Hola {$nombre}, le escribimos de {$consultorio}. Le quedan tratamientos pendientes del plan \"{$presupuesto->title}\"{$deQuien}. "
            . "Cuando guste le agendamos su siguiente cita; y si tiene alguna duda, con gusto se la resolvemos."
        : "Hola {$nombre}, le escribimos de {$consultorio}. Le recordamos el plan de tratamiento \"{$presupuesto->title}\"{$deQuien} que le presentamos. "
            . "Si tiene alguna duda, con gusto se la resolvemos; cuando usted decida, aquí lo puede ver: {$liga}";

    $presupuesto->update(['last_reminded_at' => now()]);

    return redirect()->away('https://wa.me/' . $telefono . '?text=' . urlencode($mensaje));
})->name('plan.recordar');

// "Ofrecer a Diego" en el aviso de una cancelación: lo deja notificado para
// ese hueco y abre WhatsApp con el mensaje. Solo huecos que de verdad se
// cancelaron y del consultorio de quien entra.
Route::get('/doctor/lista-de-espera/{entrada}/ofrecer/{cita}', function (int $entrada, int $cita) {
    abort_unless(auth()->check(), 403);
    $clinica = auth()->user()->clinic_id;

    $hueco = \App\Models\Appointment::withoutGlobalScopes()->where('clinic_id', $clinica)
        ->where('status', 'cancelled')->findOrFail($cita);
    $espera = \App\Models\WaitlistEntry::withoutGlobalScopes()->with(['patient', 'clinic'])
        ->where('clinic_id', $clinica)->whereIn('status', ['waiting', 'notified'])->findOrFail($entrada);

    $whatsapp = $espera->ofrecer($hueco->starts_at, $hueco);
    abort_unless($whatsapp, 422, 'El paciente no tiene teléfono.');

    return redirect()->away($whatsapp);
})->middleware('auth')->name('lista-espera.ofrecer');

// El odontograma para imprimir o guardar en PDF desde el navegador. Se busca
// por id dentro del consultorio de quien entra: el de otro consultorio no existe.
Route::get('/doctor/odontogramas/{odontogram}/imprimir', function (int $odontogram) {
    abort_unless(auth()->check(), 403);

    $odonto = \App\Models\Odontogram::with(['teeth', 'patient', 'doctor.user', 'clinic'])
        ->where('clinic_id', auth()->user()->clinic_id)
        ->findOrFail($odontogram);

    $anterior = \App\Models\Odontogram::where('clinic_id', $odonto->clinic_id)
        ->where('patient_id', $odonto->patient_id)
        ->where('id', '!=', $odonto->id)
        ->whereDate('evaluation_date', '<=', $odonto->evaluation_date)
        ->orderByDesc('evaluation_date')->orderByDesc('id')
        ->first();

    return view('odontograma.imprimir', [
        'odonto' => $odonto,
        'anterior' => $anterior,
        'cambios' => $anterior ? \App\Support\OdontogramaClinico::cambios($anterior, $odonto) : [],
    ]);
})->name('odontograma.imprimir');

Route::get('/doctor/receta/{prescription}/pdf', function (\App\Models\Prescription $prescription) {
    abort_unless(auth()->check() && auth()->user()->clinic_id === $prescription->clinic_id, 403);
    $prescription->load(['patient', 'doctor.user', 'doctor.clinic', 'items']);

    // Sin cédula ni institución del título la receta no lleva lo que pide la
    // ley (RIS art. 29; reglamento de atención médica, art. 64). En vez de
    // imprimirla incompleta, se manda a completar el perfil.
    if ($faltan = \App\Support\Receta::datosQueFaltan($prescription->doctor)) {
        return redirect(\App\Filament\Doctor\Pages\PerfilProfesional::getUrl(panel: 'doctor'))
            ->with('falta_para_receta', $faltan);
    }
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.prescription', ['prescription' => $prescription]);
    return $pdf->stream("receta-{$prescription->id}.pdf");
})->middleware('auth')->name('prescription.pdf');

// Botones del tablero de Omar (cactus-seguimiento): abren WhatsApp con el mensaje armado; "Sí, lo envié"
// (registrar) lo anota en el CRM igual que la cola del día. Solo el vendedor dueño del prospecto, con su sesión.
Route::get('/tablero/enviar/{prospecto}', [LigaDelTableroController::class, 'enviar'])->name('ventas.enviar');
Route::get('/tablero/responder/{prospecto}', [LigaDelTableroController::class, 'responder'])->name('ventas.responder');
Route::get('/tablero/registrar/{prospecto}', [LigaDelTableroController::class, 'registrar'])->name('ventas.registrar');
