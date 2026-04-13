<?php

namespace App\Filament\Sales\Widgets;

use App\Models\Prospect;
use Filament\Widgets\Widget;

class PendingFollowupsWidget extends Widget
{
    protected static ?int $sort = -5;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.sales.widgets.pending-followups';

    public function getProspects(): array
    {
        return Prospect::where('assigned_to_sales_rep_id', auth()->id())
            ->whereNotIn('status', ['converted', 'lost'])
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('next_contact_at')
                        ->where('next_contact_at', '<=', now()->endOfDay());
                })->orWhere(function ($q2) {
                    $q2->where('contact_day', 0)
                        ->where('status', 'new');
                });
            })
            ->orderBy('next_contact_at')
            ->limit(10)
            ->get()
            ->map(fn (Prospect $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'clinic' => $p->clinic_name,
                'phone' => $p->phone,
                'specialty' => $p->specialty,
                'day' => $p->contact_day,
                'status' => $p->status,
                'overdue' => $p->next_contact_at?->isPast() ?? false,
                'wa_url' => $this->buildWhatsAppUrl($p),
            ])
            ->toArray();
    }

    private function buildWhatsAppUrl(Prospect $p): string
    {
        $phone = preg_replace('/[\s\-\(\)\+]/', '', $p->phone ?? '');
        if (strlen($phone) === 10) $phone = '52' . $phone;
        $name = explode(' ', trim($p->name))[0] ?? '';

        $msg = match ($p->contact_day) {
            0, 1 => "Hola {$name}, soy de DocFacil. Queria preguntarle: como lleva el control de citas y expedientes? Le puedo mostrar algo rapido que le ahorra 2 horas al dia.",
            3 => "Hola {$name}, le doy seguimiento. Doctores que usan DocFacil recuperan 8-12 citas/mes con recordatorios WhatsApp. Son \$4,000+ extra por \$149/mes. Le interesa una demo de 10 min?",
            7 => "{$name}, ultimo mensaje. Le dejo acceso gratuito: https://docfacil.tu-app.co/doctor/register - Si necesita algo, aqui estoy.",
            default => "Hola {$name}, soy de DocFacil. Queria saber si sigue con el pendiente de organizar su consultorio. Sigo disponible para una demo rapida.",
        };

        return "https://wa.me/{$phone}?text=" . urlencode($msg);
    }
}
