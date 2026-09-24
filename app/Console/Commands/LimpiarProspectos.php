<?php

namespace App\Console\Commands;

use App\Models\Prospect;
use Illuminate\Console\Command;

/**
 * Separa lo que de verdad pasó con cada prospecto de lo que nunca pasó.
 *
 * Dos mentiras se habían acumulado en la base:
 *
 * 1. El envío de correos avanzaba el `status` solo, cada hora, sin que nadie
 *    hablara con el prospecto. Quedaron 1,313 marcados como contactados con el
 *    día de cadencia en cero, y con ellos la lista de seguimientos pendientes
 *    dejó de servir: el vendedor abría el panel y no sabía a quién escribirle.
 * 2. Los CSV de junio pusieron `has_whatsapp = 1` en todos los renglones, por
 *    defecto, sin verificar nada. De una muestra de esos lotes, 9 de cada 10
 *    números no existían en WhatsApp.
 *
 * Lo que este comando NO hace: borrar. Un correo enviado sigue en
 * `lifecycle_emails`, y las notas de cada prospecto no se tocan. Solo se
 * corrigen las tres columnas que decidían la cola de trabajo.
 */
class LimpiarProspectos extends Command
{
    protected $signature = 'docfacil:limpiar-prospectos {--dry-run : Enseña qué haría, sin escribir}';

    protected $description = 'Deja el estado de los prospectos reflejando solo lo que hizo una persona';

    public function handle(): int
    {
        $ensayo = (bool) $this->option('dry-run');

        if ($ensayo) {
            $this->warn('Ensayo: no se va a escribir nada.');
        }

        $contactosFalsos = $this->contactosQueNuncaFueron($ensayo);
        $perdidosSinIntentar = $this->perdidosQueNadieIntento($ensayo);
        $fechasFalsas = $this->fechasDeContactoQueNoOcurrio($ensayo);
        $palomitasDudosas = $this->palomitasSinVerificar($ensayo);

        $this->newLine();
        $this->line("Contactos que nunca fueron: {$contactosFalsos}");
        $this->line("Perdidos sin que nadie intentara, recuperados: {$perdidosSinIntentar}");
        $this->line("Fechas de contacto que no ocurrió, borradas: {$fechasFalsas}");
        $this->line("Palomitas de WhatsApp sin verificar: {$palomitasDudosas}");

        if ($ensayo && ($contactosFalsos || $palomitasDudosas)) {
            $this->newLine();
            $this->info('Vuelve a correrlo sin --dry-run para aplicarlo.');
        }

        return self::SUCCESS;
    }

    /**
     * Los que el correo marcó como contactados sin que nadie les escribiera.
     *
     * La señal es el día de cadencia en cero: cada envío humano lo avanza. Se
     * respeta a quien ya contestó, a quien va más adelante en el embudo y a
     * quien fue contactado por un canal de persona.
     */
    private function contactosQueNuncaFueron(bool $ensayo): int
    {
        $consulta = Prospect::query()
            ->where('status', 'contacted')
            ->where('contact_day', 0)
            ->whereNull('replied_at')
            ->where(function ($q) {
                $q->whereNull('last_contact_method')->orWhere('last_contact_method', 'email');
            })
            ->where(function ($q) {
                $q->whereNotNull('outreach_started_at')->orWhereNotNull('next_contact_at');
            });

        $cuantos = (clone $consulta)->count();

        if (! $ensayo && $cuantos) {
            $consulta->update([
                'status' => 'new',
                'outreach_started_at' => null,
                'next_contact_at' => null,
                'contacted_at' => null,
                'updated_at' => now(),
            ]);
        }

        return $cuantos;
    }

    /**
     * Los que el correo dio por perdidos sin que nadie los intentara.
     *
     * El tercer correo de la secuencia los marcaba como perdidos. Perdido
     * debería significar una sola cosa: que dijeron que no. Se respeta a quien
     * contestó, a quien ya iba avanzado en la cadencia, a quien puso una
     * objeción, y a los números que verificamos y no existen en WhatsApp.
     */
    private function perdidosQueNadieIntento(bool $ensayo): int
    {
        $consulta = Prospect::query()
            ->where('status', 'lost')
            ->where('contact_day', 0)
            ->whereNull('replied_at')
            ->where(function ($q) {
                $q->whereNull('objections_faced')->orWhere('objections_faced', '[]');
            })
            ->where(function ($q) {
                $q->whereNull('notes')->orWhere('notes', 'not like', '%sin_whatsapp%');
            });

        $cuantos = (clone $consulta)->count();

        if (! $ensayo && $cuantos) {
            $consulta->update([
                'status' => 'new',
                'outreach_started_at' => null,
                'next_contact_at' => null,
                'contacted_at' => null,
                'updated_at' => now(),
            ]);
        }

        return $cuantos;
    }

    /**
     * La marca de "aquí empezó la prospección" en quien sigue siendo nuevo.
     *
     * No cambia su estado —ya está bien— pero la fecha miente, y de ella
     * dependen los días transcurridos que se muestran en el panel.
     */
    private function fechasDeContactoQueNoOcurrio(bool $ensayo): int
    {
        $consulta = Prospect::query()
            ->where('status', 'new')
            ->where('contact_day', 0)
            ->whereNull('replied_at')
            ->where(function ($q) {
                $q->whereNotNull('outreach_started_at')->orWhereNotNull('next_contact_at');
            });

        $cuantos = (clone $consulta)->count();

        if (! $ensayo && $cuantos) {
            $consulta->update([
                'outreach_started_at' => null,
                'next_contact_at' => null,
                'contacted_at' => null,
                'updated_at' => now(),
            ]);
        }

        return $cuantos;
    }

    /**
     * La palomita que vino del CSV y no de abrir el chat.
     *
     * Verificado quiere decir que alguien abrió la conversación y vio si el
     * número existe. Eso queda anotado en las notas del prospecto, así que lo
     * que no lo traiga pasa a nulo: no sabemos.
     */
    private function palomitasSinVerificar(bool $ensayo): int
    {
        $consulta = Prospect::query()
            ->whereNotNull('has_whatsapp')
            ->where(function ($q) {
                $q->whereNull('notes')
                    ->orWhere(function ($q) {
                        $q->where('notes', 'not like', '%verificado_wa%')
                            ->where('notes', 'not like', '%sin_whatsapp%');
                    });
            });

        $cuantos = (clone $consulta)->count();

        if (! $ensayo && $cuantos) {
            $consulta->update(['has_whatsapp' => null, 'updated_at' => now()]);
        }

        return $cuantos;
    }
}
