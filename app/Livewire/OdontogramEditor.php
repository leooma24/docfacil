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
    /**
     * Herramienta activa. Ademas de las condiciones (decay, filling, ...)
     * existe INSPECCIONAR, que es el modo por defecto: al entrar, tocar un
     * diente solo lo abre para verlo, sin cambiarle nada.
     *
     * Antes el default era 'healthy' y applyTool() lo trataba como
     * inspeccion, asi que el boton "Sano" de la barra no despintaba nada:
     * el doctor que se equivocaba no tenia como corregirlo desde ahi.
     */
    public const INSPECCIONAR = 'inspect';

    public string $activeTool = self::INSPECCIONAR;

    // Dientes adultos: FDI notation
    // Cuadrante 1 (superior derecho): 18-11
    // Cuadrante 2 (superior izquierdo): 21-28
    // Cuadrante 3 (inferior izquierdo): 31-38
    // Cuadrante 4 (inferior derecho): 41-48

    public array $upperRight = [18, 17, 16, 15, 14, 13, 12, 11];
    public array $upperLeft = [21, 22, 23, 24, 25, 26, 27, 28];
    public array $lowerLeft = [31, 32, 33, 34, 35, 36, 37, 38];
    public array $lowerRight = [48, 47, 46, 45, 44, 43, 42, 41];

    // Dientes de leche: cuadrantes 5 a 8, en el mismo acomodo.
    public array $upperRightTemp = [55, 54, 53, 52, 51];
    public array $upperLeftTemp = [61, 62, 63, 64, 65];
    public array $lowerLeftTemp = [71, 72, 73, 74, 75];
    public array $lowerRightTemp = [85, 84, 83, 82, 81];

    /** permanente, temporal o mixta: qué filas se dibujan. */
    public string $denticion = 'permanente';

    public function mount(?int $odontogramId = null): void
    {
        $this->odontogramId = $odontogramId;

        // Initialize all 32 teeth as healthy
        $allTeeth = array_merge(
            $this->upperRight, $this->upperLeft, $this->lowerLeft, $this->lowerRight,
            $this->upperRightTemp, $this->upperLeftTemp, $this->lowerLeftTemp, $this->lowerRightTemp,
        );

        foreach ($allTeeth as $num) {
            $this->teeth[$num] = [
                'condition' => 'healthy',
                'notes' => '',
                'surfaces' => OdontogramTooth::carasVacias(),
            ];
        }

        // Load existing data (scoped to user's clinic)
        if ($odontogramId) {
            $odontogram = Odontogram::with('teeth')
                ->where('clinic_id', auth()->user()->clinic_id)
                ->find($odontogramId);
            if ($odontogram) {
                // Si ya trae dientes de leche marcados, se abre en mixta para verlos.
                if ($odontogram->teeth->contains(fn ($t) => $t->tooth_number >= 51)) {
                    $this->denticion = 'mixta';
                }

                foreach ($odontogram->teeth as $tooth) {
                    $this->teeth[$tooth->tooth_number] = [
                        'condition' => $tooth->condition,
                        'notes' => $tooth->notes ?? '',
                        'surfaces' => $tooth->caras(),
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
        if ($this->activeTool === self::INSPECCIONAR) {
            $this->selectedCondition = $this->teeth[$toothNumber]['condition'] ?? 'healthy';
        } else {
            // "Sano" sobre el diente lo deja limpio, caras incluidas.
            if ($this->activeTool === 'healthy') {
                $this->teeth[$toothNumber]['surfaces'] = OdontogramTooth::carasVacias();
            }
            $this->teeth[$toothNumber]['condition'] = $this->activeTool;
            $this->selectedCondition = $this->activeTool;
            $this->dispatch('teeth-updated', teeth: $this->teeth);
        }

        $this->selectedTooth = $toothNumber;
        $this->toothNotes = $this->teeth[$toothNumber]['notes'] ?? '';
    }

    /**
     * Toque sobre una cara del diagrama de cinco caras. Caries, obturación,
     * sellante y pendiente se quedan en esa cara; "Sano" la limpia; lo que es
     * del diente entero (extracción, corona…) se aplica al diente.
     */
    public function applySurface(int $toothNumber, string $cara): void
    {
        if (! array_key_exists($cara, OdontogramTooth::CARAS) || ! isset($this->teeth[$toothNumber])) {
            return;
        }

        $herramienta = $this->activeTool;
        $esDeCara = $herramienta === 'healthy' || in_array($herramienta, OdontogramTooth::DE_CARA, true);

        if ($herramienta === self::INSPECCIONAR || ! $esDeCara) {
            $this->applyTool($toothNumber);

            return;
        }

        $diente = &$this->teeth[$toothNumber];
        $diente['surfaces'] = array_merge(OdontogramTooth::carasVacias(), $diente['surfaces'] ?? []);
        $diente['surfaces'][$cara] = $herramienta === 'healthy' ? null : $herramienta;
        $diente['condition'] = OdontogramTooth::resumen($diente['condition'] ?? null, $diente['surfaces']);
        unset($diente);

        $this->selectedTooth = $toothNumber;
        $this->selectedCondition = $this->teeth[$toothNumber]['condition'];
        $this->toothNotes = $this->teeth[$toothNumber]['notes'] ?? '';
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

    public function setDenticion(string $denticion): void
    {
        if (in_array($denticion, ['permanente', 'temporal', 'mixta'], true)) {
            $this->denticion = $denticion;
        }
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
