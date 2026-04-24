<?php

namespace App\Filament\Doctor\Pages;

use App\Models\FeatureRequest;
use App\Models\FeatureVote;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Roadmap comunitario — los dentistas proponen features, votan con
 * willingness-to-pay, y cada mes Omar elige dos ganadoras (una paga
 * + una gratis) para construir. Convierte usuarios en accionistas
 * del producto.
 */
class Roadmap extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationLabel = 'Roadmap';

    protected static ?string $title = 'Roadmap comunitario';

    protected static ?string $slug = 'roadmap';

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?int $navigationSort = 11;

    protected static string $view = 'filament.doctor.pages.roadmap';

    public string $activeTab = 'proposed';

    // Propose form state
    public bool $showProposeModal = false;
    public string $newTitle = '';
    public string $newDescription = '';
    public string $newPriceTier = 'free';

    // Vote modal state
    public ?int $votingFeatureId = null;
    public string $voteWillingness = 'free';

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['proposed', 'in_progress', 'shipped']) ? $tab : 'proposed';
    }

    public function getFeaturesProperty()
    {
        $clinicId = auth()->user()->clinic_id;
        $votedIds = FeatureVote::where('clinic_id', $clinicId)->pluck('feature_request_id')->toArray();

        $query = FeatureRequest::with('submittedByUser', 'submittedByClinic');

        match ($this->activeTab) {
            'proposed' => $query->open()->orderByDesc('votes_count'),
            'in_progress' => $query->inProgress()->orderByDesc('votes_count'),
            'shipped' => $query->shipped()->orderByDesc('shipped_at'),
            default => null,
        };

        return $query->get()->map(function (FeatureRequest $f) use ($votedIds) {
            $data = $f->toArray();
            $data['has_voted'] = in_array($f->id, $votedIds, true);
            $data['author_name'] = $f->submittedByUser?->name ?? 'Doctor';
            $data['author_initials'] = strtoupper(mb_substr($f->submittedByUser?->name ?? 'D', 0, 1));
            $data['author_clinic'] = $f->submittedByClinic?->name ?? '';
            $data['is_mine'] = $f->submitted_by_user_id === auth()->id();
            $data['price_label'] = FeatureRequest::PRICE_TIERS[$f->proposed_price_tier] ?? null;
            return $data;
        })->toArray();
    }

    public function getStatsProperty(): array
    {
        return [
            'total_proposed' => FeatureRequest::count(),
            'total_shipped' => FeatureRequest::shipped()->count(),
            'my_votes' => FeatureVote::where('clinic_id', auth()->user()->clinic_id)->count(),
            'my_proposals' => FeatureRequest::where('submitted_by_user_id', auth()->id())->count(),
        ];
    }

    public function openProposeModal(): void
    {
        $this->showProposeModal = true;
        $this->newTitle = '';
        $this->newDescription = '';
        $this->newPriceTier = 'free';
    }

    public function closeProposeModal(): void
    {
        $this->showProposeModal = false;
    }

    public function submitProposal(): void
    {
        $this->validate([
            'newTitle' => 'required|string|min:10|max:160',
            'newDescription' => 'required|string|min:30|max:2000',
            'newPriceTier' => 'required|in:' . implode(',', array_keys(FeatureRequest::PRICE_TIERS)),
        ], [], [
            'newTitle' => 'Título',
            'newDescription' => 'Descripción',
            'newPriceTier' => 'Precio sugerido',
        ]);

        $user = auth()->user();

        $request = FeatureRequest::create([
            'submitted_by_user_id' => $user->id,
            'submitted_by_clinic_id' => $user->clinic_id,
            'title' => $this->newTitle,
            'description' => $this->newDescription,
            'proposed_price_tier' => $this->newPriceTier,
            'status' => 'proposed',
        ]);

        // El proponente vota automatico a su propia idea con el precio que sugirio
        FeatureVote::create([
            'feature_request_id' => $request->id,
            'clinic_id' => $user->clinic_id,
            'user_id' => $user->id,
            'willingness_to_pay' => $this->newPriceTier,
        ]);

        $this->showProposeModal = false;
        $this->newTitle = '';
        $this->newDescription = '';

        Notification::make()
            ->title('¡Idea enviada al roadmap!')
            ->body('Compártela con tus colegas para que voten — mientras más votos, más probable que gane el próximo mes.')
            ->success()
            ->send();
    }

    public function openVoteModal(int $featureId): void
    {
        $clinicId = auth()->user()->clinic_id;
        $existingVote = FeatureVote::where('feature_request_id', $featureId)
            ->where('clinic_id', $clinicId)
            ->first();

        if ($existingVote) {
            $this->removeVote($featureId);
            return;
        }

        $this->votingFeatureId = $featureId;
        $this->voteWillingness = 'free';
    }

    public function closeVoteModal(): void
    {
        $this->votingFeatureId = null;
    }

    public function submitVote(): void
    {
        if (!$this->votingFeatureId) return;

        $this->validate([
            'voteWillingness' => 'required|in:' . implode(',', array_keys(FeatureRequest::PRICE_TIERS)),
        ]);

        try {
            FeatureVote::create([
                'feature_request_id' => $this->votingFeatureId,
                'clinic_id' => auth()->user()->clinic_id,
                'user_id' => auth()->id(),
                'willingness_to_pay' => $this->voteWillingness,
            ]);

            Notification::make()
                ->title('Tu voto cuenta 🗳️')
                ->body('Gracias por ayudar a decidir qué construimos este mes.')
                ->success()
                ->send();
        } catch (\Illuminate\Database\QueryException $e) {
            // 23000 = unique constraint violation (vote ya existia)
            if ($e->getCode() !== '23000') throw $e;
        }

        $this->votingFeatureId = null;
    }

    public function removeVote(int $featureId): void
    {
        $deleted = FeatureVote::where('feature_request_id', $featureId)
            ->where('clinic_id', auth()->user()->clinic_id)
            ->delete();

        if ($deleted) {
            Notification::make()
                ->title('Voto retirado')
                ->body('Puedes volver a votar cuando quieras.')
                ->warning()
                ->send();
        }
    }
}
