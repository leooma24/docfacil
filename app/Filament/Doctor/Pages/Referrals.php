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
        $msg = urlencode("Hola! Te recomiendo DocFácil para tu consultorio. Es un software para gestionar citas, pacientes, recetas y más. Usa mi código *{$code}* al registrarte y ambos recibimos 15 días gratis extra.\n\nRegístrate aquí: " . $this->getReferralLink() . "\n\ndocfacil.tu-app.co");
        return "https://wa.me/?text={$msg}";
    }

    public function getReferralsProperty(): array
    {
        return Referral::where('referrer_id', auth()->id())
            ->with('referredUser')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($r) => [
                'email' => $r->referred_email,
                'name' => $r->referredUser?->name ?? '-',
                'status' => $r->status,
                'reward' => $r->reward_days ? "+{$r->reward_days} días" : '-',
                'date' => $r->created_at->format('d/m/Y'),
            ])
            ->toArray();
    }

    public function getTotalRewardsProperty(): int
    {
        return Referral::where('referrer_id', auth()->id())
            ->where('status', 'rewarded')
            ->sum('reward_days');
    }
}
