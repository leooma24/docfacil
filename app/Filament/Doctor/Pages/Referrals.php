<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Referral;
use Filament\Pages\Page;

class Referrals extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Invitar colegas';

    protected static ?string $title = 'Invitar colegas';

    protected static ?string $slug = 'referidos';

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.doctor.pages.referrals';

    public function getReferralCode(): string
    {
        return auth()->user()->referral_code ?? 'Sin código';
    }

    public function getReferralLink(): string
    {
        return url('/doctor/register?ref=' . $this->getReferralCode());
    }

    public function getWhatsAppShareLink(): string
    {
        $code = $this->getReferralCode();
        $link = $this->getReferralLink();
        $msg = urlencode(
            "Hola! Te recomiendo *DocFácil* para tu consultorio. Software para agenda, pacientes, recetas PDF, cobros por WhatsApp y más.\n\n".
            "Usa mi código *{$code}* al registrarte y ambos ganamos:\n\n".
            "*Tú*: 30 días gratis (vs 15 normales)\n".
            "*Yo*: 15 días extra + 1 mes gratis por cada mes que pagues (hasta 12 meses)\n\n".
            "Regístrate aquí: {$link}"
        );
        return "https://wa.me/?text={$msg}";
    }

    public function getReferralsProperty(): array
    {
        return Referral::where('referrer_id', auth()->id())
            ->with('referredUser.clinic')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'email' => $r->referred_email,
                'name' => $r->referredUser?->name ?? '-',
                'clinic_name' => $r->referredUser?->clinic?->name ?? '-',
                'plan' => $r->referredUser?->clinic?->plan ?? 'free',
                'status' => $r->status,
                'cascade_rewards' => (int) $r->cascade_rewards_granted,
                'registration_reward' => $r->reward_days ? "+{$r->reward_days}d" : '-',
                'date' => $r->created_at->format('d/m/Y'),
            ])
            ->toArray();
    }

    public function getStatsProperty(): array
    {
        $totalReferred = Referral::where('referrer_id', auth()->id())->count();
        $paidReferred = Referral::where('referrer_id', auth()->id())
            ->whereHas('referredUser.clinic', fn ($q) => $q->whereIn('plan', ['basico', 'profesional', 'clinica']))
            ->count();
        $registrationDays = (int) Referral::where('referrer_id', auth()->id())
            ->where('status', 'rewarded')
            ->sum('reward_days');
        $cascadeRewards = (int) Referral::where('referrer_id', auth()->id())
            ->sum('cascade_rewards_granted');
        $totalDays = $registrationDays + ($cascadeRewards * Referral::CASCADE_REWARD_DAYS);

        return [
            'total_referred' => $totalReferred,
            'paid_referred' => $paidReferred,
            'cascade_rewards' => $cascadeRewards,
            'total_days' => $totalDays,
            'total_months' => round($totalDays / 30, 1),
            'cap' => Referral::CASCADE_REWARD_CAP,
        ];
    }

    public function getLeaderboardProperty(): array
    {
        return Referral::query()
            ->selectRaw('referrer_id, COUNT(*) as total, SUM(cascade_rewards_granted) as cascade_total')
            ->groupBy('referrer_id')
            ->orderByDesc('cascade_total')
            ->orderByDesc('total')
            ->limit(10)
            ->with('referrer')
            ->get()
            ->filter(fn ($r) => $r->referrer !== null)
            ->values()
            ->map(fn ($r, $i) => [
                'position' => (int) $i + 1,
                'name' => $r->referrer?->name ?? 'Doctor',
                'initials' => strtoupper(substr($r->referrer?->name ?? 'D', 0, 1)),
                'referred' => (int) $r->total,
                'cascade_months' => (int) $r->cascade_total,
                'is_me' => $r->referrer_id === auth()->id(),
            ])
            ->toArray();
    }
}
