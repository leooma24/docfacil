<?php

use App\Models\Prospect;
use App\Models\ProspectMensaje;
use App\Support\MensajesDeVenta;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Cada mensaje de venta que se manda, con su paso y su versión.
 *
 * Hasta el 5-oct-2026 el CRM guardaba el último envío y la respuesta, pero no
 * qué mensaje se mandó: no había forma de saber si el primer mensaje corto o
 * el segundo con video funcionaban mejor. Lo que ya se había mandado se carga
 * estimado por fechas (estimado = true): el primer mensaje con la fecha del
 * inicio de la cadencia, el último con la del último envío y los de en medio
 * a los días que marca la cadencia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('paso');
            $table->string('version', 40);
            $table->string('canal', 20)->default('whatsapp');
            $table->boolean('estimado')->default(false);
            $table->dateTime('enviado_at');
            $table->timestamps();
            $table->index(['prospect_id', 'enviado_at']);
            $table->index(['user_id', 'version']);
        });

        $this->cargarLoDeAntes();
    }

    /** Los envíos que ya habían pasado, estimados. No repite al que ya tiene. */
    public function cargarLoDeAntes(): void
    {
        $pasos = array_keys(Prospect::CADENCE);

        Prospect::withoutGlobalScopes()
            ->where('last_contact_method', 'whatsapp')
            ->where('contact_day', '>', 0)
            ->whereDoesntHave('mensajes')
            ->get()
            ->each(function (Prospect $p) use ($pasos) {
                $inicio = $p->outreach_started_at ?? $p->contacted_at ?? $p->last_followup_at;
                if (! $inicio) {
                    return;
                }

                $enviados = array_values(array_filter($pasos, fn ($paso) => $paso < $p->contact_day));
                foreach ($enviados as $i => $paso) {
                    $cuando = $i === count($enviados) - 1 && $p->last_followup_at
                        ? $p->last_followup_at
                        : Carbon::parse($inicio)->addDays($paso);

                    ProspectMensaje::create([
                        'prospect_id' => $p->id,
                        'user_id' => $p->assigned_to_sales_rep_id,
                        'paso' => $paso,
                        'version' => MensajesDeVenta::version($paso, $cuando),
                        'canal' => 'whatsapp',
                        'estimado' => true,
                        'enviado_at' => $cuando,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_mensajes');
    }
};
