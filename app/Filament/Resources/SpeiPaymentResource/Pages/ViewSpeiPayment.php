<?php

namespace App\Filament\Resources\SpeiPaymentResource\Pages;

use App\Filament\Resources\SpeiPaymentResource;
use App\Models\SpeiPayment;
use App\Services\SpeiReviewService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSpeiPayment extends ViewRecord
{
    protected static string $resource = SpeiPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Aprobar y activar plan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === SpeiPayment::STATUS_PENDING)
                ->form([
                    Forms\Components\Textarea::make('review_notes')
                        ->label('Nota interna (opcional)')
                        ->rows(2),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    app(SpeiReviewService::class)->approve($this->record, auth()->user(), $data['review_notes'] ?? null);
                    Notification::make()->title('Pago aprobado y plan activado')->success()->send();
                    $this->refreshFormData(['status', 'reviewed_by', 'reviewed_at', 'review_notes', 'plan_activated_until']);
                }),
            Actions\Action::make('reject')
                ->label('Rechazar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === SpeiPayment::STATUS_PENDING)
                ->form([
                    Forms\Components\Textarea::make('review_notes')
                        ->label('Motivo del rechazo (se envía al cliente)')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    app(SpeiReviewService::class)->reject($this->record, auth()->user(), $data['review_notes']);
                    Notification::make()->title('Pago rechazado')->warning()->send();
                    $this->refreshFormData(['status', 'reviewed_by', 'reviewed_at', 'review_notes']);
                }),
        ];
    }
}
