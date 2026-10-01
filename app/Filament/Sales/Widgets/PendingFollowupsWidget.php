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
                'wa_url' => \App\Filament\Sales\Resources\ProspectResource::buildContextualWhatsappUrl($p),
            ])
            ->toArray();
    }

    // El mensaje sale de ProspectResource::buildContextualWhatsappUrl(): aquí
    // vivía una segunda copia, con otro texto y la URL escrita a mano. Dos
    // juegos de plantillas es como se vuelven a separar.
}
