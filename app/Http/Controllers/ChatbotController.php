<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NotifiesNewLead;
use App\Mail\WelcomeOnboardingMail;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Prospect;
use App\Models\User;
use App\Services\ChatbotSalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ChatbotController extends Controller
{
    use NotifiesNewLead;

    public function message(Request $request, ChatbotSalesService $bot)
    {
        $data = $request->validate([
            'session_id' => 'required|string|size:36',
            'message' => 'required|string|max:2000',
        ]);

        $result = $bot->respond($data['session_id'], $data['message']);

        if (!empty($result['disabled'])) {
            return response()->json([
                'disabled' => true,
                'reason' => $result['reason'] ?? 'off',
                'reply' => $this->offlineReply($result['reason'] ?? 'off'),
            ]);
        }

        return response()->json([
            'reply' => $result['reply'],
            'tags' => $result['tags'],
        ]);
    }

    public function close(Request $request, ChatbotSalesService $bot)
    {
        $data = $request->validate([
            'session_id' => 'required|string|size:36',
            'data' => 'required|array',
            'data.name' => 'required|string|max:255',
            'data.email' => 'required|email|max:255',
            'data.phone' => 'nullable|string|max:50',
            'data.clinic_name' => 'nullable|string|max:255',
            'data.specialty' => 'nullable|string|max:255',
            'data.city' => 'nullable|string|max:255',
        ]);

        $history = $bot->getHistory($data['session_id']);
        $leadScore = $bot->calculateLeadScore($data['data'], $history);

        $prospect = Prospect::updateOrCreate(
            ['email' => $data['data']['email']],
            [
                'name' => $data['data']['name'],
                'phone' => $data['data']['phone'] ?? null,
                'clinic_name' => $data['data']['clinic_name'] ?? null,
                'specialty' => $data['data']['specialty'] ?? null,
                'city' => $data['data']['city'] ?? null,
                'source' => 'chatbot_landing',
                'status' => 'new',
                'conversation_log' => $history,
                'lead_score' => $leadScore,
                'assigned_to_sales_rep_id' => $this->defaultSalesRepId(),
            ]
        );

        $this->notifyAdminNewLead($prospect, 'Nuevo lead chatbot');
        $bot->clearHistory($data['session_id']);

        return response()->json([
            'ok' => true,
            'trial_url' => '/doctor/register?' . http_build_query([
                'name' => $data['data']['name'],
                'email' => $data['data']['email'],
            ]),
        ]);
    }

    public function createAccount(Request $request, ChatbotSalesService $bot)
    {
        $data = $request->validate([
            'session_id' => 'required|string|size:36',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:72',
            'clinic_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:50',
            'specialty' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:255',
            'terms_accepted' => 'required|accepted',
        ]);

        $history = $bot->getHistory($data['session_id']);
        $salesRepId = $this->defaultSalesRepId();

        try {
            $user = DB::transaction(function () use ($data, $history, $bot, $salesRepId) {
                $clinic = new Clinic();
                $clinic->forceFill([
                    'name' => $data['clinic_name'],
                    'phone' => $data['phone'] ?? null,
                    'city' => $data['city'] ?? null,
                    'plan' => 'free',
                    'trial_ends_at' => now()->addDays(15),
                    'sold_by_user_id' => $salesRepId,
                    'sold_at' => $salesRepId ? now() : null,
                ])->save();

                $user = User::forceCreate([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt($data['password']),
                    'role' => 'doctor',
                    'clinic_id' => $clinic->id,
                    'terms_accepted_at' => now(),
                ]);

                Doctor::create([
                    'user_id' => $user->id,
                    'clinic_id' => $clinic->id,
                    'specialty' => $data['specialty'] ?? null,
                    'license_number' => $data['license_number'],
                ]);

                $leadScore = $bot->calculateLeadScore($data, $history) + 25;
                $prospect = Prospect::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'phone' => $data['phone'] ?? null,
                        'clinic_name' => $data['clinic_name'],
                        'specialty' => $data['specialty'] ?? null,
                        'city' => $data['city'] ?? null,
                        'source' => 'chatbot_landing',
                        'status' => 'converted',
                        'converted_at' => now(),
                        'converted_clinic_id' => $clinic->id,
                        'assigned_to_sales_rep_id' => $salesRepId,
                        'conversation_log' => $history,
                        'lead_score' => min(100, $leadScore),
                    ]
                );

                try {
                    Mail::to($user->email)->send(new WelcomeOnboardingMail($user));
                } catch (\Throwable $e) {
                    Log::warning('WelcomeOnboardingMail failed', ['error' => $e->getMessage()]);
                }

                // User implements MustVerifyEmail — dispatch Registered para que
                // Laravel mande el correo de verificacion. forceCreate no lo
                // dispara automaticamente.
                event(new \Illuminate\Auth\Events\Registered($user));

                $this->notifyAdminNewLead(
                    $prospect,
                    '¡Conversión por chatbot!',
                    "Nueva clínica creada desde el chat del landing.\nClinic ID: {$clinic->id}\nUser ID: {$user->id}"
                );

                return $user;
            });

            $bot->clearHistory($data['session_id']);

            // Genera token one-time-use guardado en DB (hash SHA-256).
            // El token vive solo 15 minutos y se consume en el primer uso.
            // Evita el patron anterior donde la URL firmada era un bearer
            // token reusable sobre cualquier user ID por su TTL.
            $token = $user->generateChatbotAutologinToken();

            return response()->json([
                'ok' => true,
                'login_url' => route('chatbot.autoLogin', ['token' => $token]),
            ]);
        } catch (\Throwable $e) {
            Log::error('Chatbot createAccount failed', ['error' => $e->getMessage()]);
            return response()->json([
                'ok' => false,
                'error' => 'No pude crear tu cuenta. Intenta desde el registro normal o escríbeme por WhatsApp.',
            ], 500);
        }
    }

    public function autoLogin(Request $request, string $token)
    {
        // Requires a token length sanity check before hitting DB to avoid
        // enumeration + rate-limit sidechannels.
        if (strlen($token) !== 64 || !ctype_xdigit($token)) {
            abort(403, 'Enlace inválido');
        }

        $hashed = hash('sha256', $token);
        $user = User::where('chatbot_autologin_token', $hashed)
            ->where('chatbot_autologin_expires_at', '>', now())
            ->first();

        if (!$user || !$user->consumeChatbotAutologinToken($token)) {
            abort(403, 'Enlace expirado o ya usado');
        }

        Auth::login($user);
        return redirect('/doctor');
    }

    protected function defaultSalesRepId(): ?int
    {
        return User::where('email', 'ventas@docfacil.com')
            ->where('role', 'sales')
            ->value('id');
    }

    protected function offlineReply(string $reason): string
    {
        return match ($reason) {
            'daily_limit' => 'Llegamos al máximo de conversaciones por hoy. Déjame tu nombre, email y WhatsApp en el formulario de contacto y te respondemos por ahí 🙏',
            default => 'El chat no está disponible ahora mismo. Mándanos un mensaje por WhatsApp o llena el formulario de contacto 🙂',
        };
    }
}
