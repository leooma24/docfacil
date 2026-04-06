<?php

namespace App\Livewire;

use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use Livewire\Component;

class OdontogramEditor extends Component
{
    public ?int $odontogramId = null;
    public array $teeth = [];
    public ?int $selectedTooth = null;
    public string $selectedCondition = 'healthy';
    public string $toothNotes = '';
    public string $activeTool = 'healthy';

    // Dientes adultos: FDI notation
    // Cuadrante 1 (superior derecho): 18-11
    // Cuadrante 2 (superior izquierdo): 21-28
    // Cuadrante 3 (inferior izquierdo): 31-38
    // Cuadrante 4 (inferior derecho): 41-48

    public array $upperRight = [18, 17, 16, 15, 14, 13, 12, 11];
    public array $upperLeft = [21, 22, 23, 24, 25, 26, 27, 28];
    public array $lowerLeft = [31, 32, 33, 34, 35, 36, 37, 38];
    public array $lowerRight = [48, 47, 46, 45, 44, 43, 42, 41];

    public function mount(?int $odontogramId = null): void
    {
        $this->odontogramId = $odontogramId;

        // Initialize all 32 teeth as healthy
        $allTeeth = array_merge($this->upperRight, $this->upperLeft, $this->lowerLeft, $this->lowerRight);

        foreach ($allTeeth as $num) {
            $this->teeth[$num] = [
                'condition' => 'healthy',
                'notes' => '',
            ];
        }

        // Load existing data
        if ($odontogramId) {
            $odontogram = Odontogram::with('teeth')->find($odontogramId);
            if ($odontogram) {
                foreach ($odontogram->teeth as $tooth) {
                    $this->teeth[$tooth->tooth_number] = [
                        'condition' => $tooth->condition,
                        'notes' => $tooth->notes ?? '',
                    ];
                }
            }
        }
    }

    public function selectTooth(int $toothNumber): void
    {
        $this->selectedTooth = $toothNumber;
        $this->selectedCondition = $this->teeth[$toothNumber]['condition'] ?? 'healthy';
        $this->toothNotes = $this->teeth[$toothNumber]['notes'] ?? '';
    }

    public function applyTool(int $toothNumber): void
    {
        $this->teeth[$toothNumber]['condition'] = $this->activeTool;
        $this->selectedTooth = $toothNumber;
        $this->selectedCondition = $this->activeTool;
        $this->dispatch('teeth-updated', teeth: $this->teeth);
    }

    public function updateTooth(): void
    {
        if ($this->selectedTooth === null) {
            return;
        }

        $this->teeth[$this->selectedTooth]['condition'] = $this->selectedCondition;
        $this->teeth[$this->selectedTooth]['notes'] = $this->toothNotes;
        $this->dispatch('teeth-updated', teeth: $this->teeth);
    }

    public function setTool(string $condition): void
    {
        $this->activeTool = $condition;
    }

    public function getTeethProperty(): array
    {
        return $this->teeth;
    }

    public function render()
    {
        return view('livewire.odontogram-editor', [
            'conditionLabels' => OdontogramTooth::conditionLabels(),
            'conditionColors' => OdontogramTooth::conditionColors(),
        ]);
    }
}
