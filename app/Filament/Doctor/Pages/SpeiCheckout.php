<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Commission;
use App\Models\SpeiPayment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SpeiCheckout extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.doctor.pages.spei-checkout';

    protected static ?string $slug = 'pago-spei';

    public string $plan = 'basico';
    public string $cycle = 'monthly';
    public string $referenceCode = '';
    public int $amount = 0;

    public ?array $data = [];

    public function mount(): void
    {
        $this->plan = request()->query('plan', 'basico');
        $this->cycle = request()->query('cycle', 'monthly');

        abort_unless(in_array($this->plan, ['basico', 'profesional', 'clinica'], true), 404);
        abort_unless(in_array($this->cycle, ['monthly', 'annual'], true), 404);

        $clinic = auth()->user()->clinic;
        abort_unless($clinic, 403);

        $this->amount = Commission::priceForCycle($this->plan, $this->cycle);
        $this->referenceCode = SpeiPayment::generateReferenceCode($clinic->id);

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('receipt')
                    ->label('Comprobante de transferencia (imagen o PDF)')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                    ->maxSize(5120) // 5 MB
                    ->disk('local') // Almacenado en storage/app — NO accesible públicamente. Contiene datos bancarios.
                    ->directory('spei-receipts/' . auth()->user()->clinic_id)
                    ->visibility('private')
                    ->required()
                    ->helperText('Sube la captura o PDF del comprobante SPEI. Se acepta JPG, PNG o PDF hasta 5 MB. Tu comprobante se guarda cifrado y solo lo vemos nosotros para aprobar el pago.'),

                Textarea::make('notes')
                    ->label('Notas (opcional)')
                    ->placeholder('Cualquier observación que te ayude a rastrear este pago')
                    ->rows(3)
                    ->maxLength(500),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        $clinic = auth()->user()->clinic;
        if (!$clinic) {
            Notification::make()->title('No se encontró tu consultorio')->danger()->send();
            return;
        }

        $receiptPath = $state['receipt'] ?? null;
        if (is_array($receiptPath)) {
            $receiptPath = reset($receiptPath) ?: null;
        }

        if (!$receiptPath) {
            Notification::make()->title('Sube el comprobante para continuar')->warning()->send();
            return;
        }

        $disk = Storage::disk('local');
        $mime = $disk->exists($receiptPath) ? $disk->mimeType($receiptPath) : null;
        $size = $disk->exists($receiptPath) ? $disk->size($receiptPath) : null;
        $original = basename($receiptPath);

        $payment = SpeiPayment::create([
            'clinic_id' => $clinic->id,
            'user_id' => auth()->id(),
            'plan' => $this->plan,
            'billing_cycle' => $this->cycle,
            'amount' => $this->amount,
            'reference_code' => $this->referenceCode,
            'receipt_path' => $receiptPath,
            'receipt_original_name' => $original,
            'receipt_mime' => $mime,
            'receipt_size_bytes' => $size ?: null,
            'client_notes' => $state['notes'] ?? null,
            'status' => SpeiPayment::STATUS_PENDING,
        ]);

        // Notificar a los admins por correo (async via queue si está disponible)
        $adminEmails = collect(explode(',', (string) config('services.spei.admin_emails')))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values()
            ->all();

        $planLabel = ucfirst($this->plan === 'profesional' ? 'Pro' : $this->plan);
        $cycleLabel = $this->cycle === 'annual' ? '(Anual)' : '(Mensual)';
        $amountFmt = number_format($this->amount, 2);
        $subject = '🟡 Nuevo pago SPEI pendiente · DocFácil · $' . $amountFmt;
        $body = sprintf(
            "Nuevo pago SPEI pendiente de aprobación:\n\nClínica: %s (#%d)\nPlan: %s %s\nMonto: $%s MXN\nReferencia: %s\n\nRevísalo en: %s",
            $clinic->name,
            $clinic->id,
            $planLabel,
            $cycleLabel,
            $amountFmt,
            $this->referenceCode,
            url('/admin/spei-payments/' . $payment->id),
        );

        foreach ($adminEmails as $email) {
            try {
                Mail::raw($body, function ($m) use ($email, $subject) {
                    $m->to($email)->subject($subject);
                });
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar correo de nuevo SPEI', ['error' => $e->getMessage()]);
            }
        }

        Notification::make()
            ->title('Comprobante recibido')
            ->body('Tu pago está en revisión. Te avisaremos en cuanto se apruebe (1-24 hrs).')
            ->success()
            ->duration(8000)
            ->send();

        $this->redirect(route('filament.doctor.pages.actualizar-plan'));
    }

    public function getTitle(): string
    {
        return 'Pago por transferencia SPEI';
    }
}
