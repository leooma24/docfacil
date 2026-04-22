<?php

namespace Database\Seeders;

use App\Models\Prospect;
use Illuminate\Database\Seeder;

/**
 * Generated seeder — batch research-dentistas-gdl-dedup-20260422.
 *
 * 29 dentistas descubiertos vía WebSearch + WebFetch, deduplicados contra prod.
 * Todos con teléfono MX de 10 dígitos. Asignados a Omar (user_id=100) con
 * source='prospecting' para que el cron send-prospect-emails los tome automático.
 * El batch identifier vive en notes.batch = 'research-dentistas-gdl-dedup-20260422' para analytics.
 *
 * Ejecutar: php artisan db:seed --class=ProspectDentistasGDLPilotSeeder --force
 */
class ProspectDentistasGDLPilotSeeder extends Seeder
{
    public function run(): void
    {
        $prospects = [
            [
                'name' => 'Dra. Montserrat Lambertiny / Dr. Juan Pedro Oliva',
                'clinic_name' => 'Ortodoncia Guadalajara (gdlortodoncia)',
                'specialty' => 'Ortodoncia',
                'city' => 'Guadalajara',
                'address' => 'Justicia 2732, Providencia, Guadalajara, Jalisco',
                'phone' => '3311537843',
                'has_whatsapp' => true,
                'email' => 'contacto@gdlortodoncia.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Providencia","website":"https:\/\/www.gdlortodoncia.com\/","source_channel":"google-search","notes":"2 ortodoncistas certificados; consultorio pequeño"}',
            ],
            [
                'name' => 'Dr. Rainier A. Rojas R.',
                'clinic_name' => 'Ortodoncia Guadalajara (Dr Rainier Rojas)',
                'specialty' => 'Ortodoncia',
                'city' => 'Guadalajara',
                'address' => 'Río de Janeiro 2637, Providencia, Guadalajara',
                'phone' => '3317412776',
                'has_whatsapp' => true,
                'email' => 'ortoguadalajara@gmail.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Providencia","instagram":"@ortodoncia.guadalajara","website":"https:\/\/ortodoncia-guadalajara.com\/","source_channel":"google-search","notes":"Maestría ortodoncia + Invisalign; también Dra. Andrea Crema"}',
            ],
            [
                'name' => 'Clinica Dental Station 2430',
                'clinic_name' => 'Clinica Dental Station 2430',
                'specialty' => 'Odontología General',
                'city' => 'Guadalajara',
                'address' => 'Manuel Acuña 2430, Ladrón de Guevara, Guadalajara',
                'phone' => '3333307817',
                'has_whatsapp' => false,
                'email' => 'contacto@dentalstation.mx',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Ladrón de Guevara","website":"https:\/\/dentalstation.mx\/","source_channel":"google-search","notes":"Multiespecialidad en una sede; tamaño consultorio"}',
            ],
            [
                'name' => 'Dra. Lorena Bandini García',
                'clinic_name' => 'SmileMed Clínica Dental',
                'specialty' => 'Odontopediatría',
                'city' => 'Guadalajara',
                'address' => 'C. José Bonifacio Andrada 2725, Providencia, 44657 Guadalajara',
                'phone' => '3336153291',
                'has_whatsapp' => false,
                'email' => 'smilemedmx@gmail.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Providencia","instagram":"@smilemed_clinica","website":"https:\/\/www.smilemed.com.mx","source_channel":"google-search","notes":"Equipo pequeño; IG activo; enfoque familiar"}',
            ],
            [
                'name' => 'Dra. Elisabeth Piccirillo',
                'clinic_name' => 'Odontología Especializada GDL',
                'specialty' => 'Odontología Estética',
                'city' => 'Zapopan',
                'address' => 'Texcoco 238, Cd del Sol, 45050 Zapopan',
                'phone' => '3339000691',
                'has_whatsapp' => true,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Ciudad del Sol","website":"https:\/\/www.odontologiaespecializadagdl.com\/","source_channel":"google-search","notes":"1 doctora titular; estética + diseño de sonrisa"}',
            ],
            [
                'name' => 'Dr. Víctor Cervantes',
                'clinic_name' => 'Odontología Moderna OM',
                'specialty' => 'Odontología General',
                'city' => 'Guadalajara',
                'address' => 'Mexicaltzingo 2032, Col. Americana, 44160 Guadalajara',
                'phone' => '3317055871',
                'has_whatsapp' => true,
                'email' => 'contacto@dentistaenguadalajara.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Americana","website":"https:\/\/dentistadeguadalajara.com\/","source_channel":"google-search","notes":"Consultorio familiar 25 años; 2 sitios paralelos"}',
            ],
            [
                'name' => 'Consultorio Dental Esp. Médicas Chapalita',
                'clinic_name' => 'Consultorio Dental Esp. Médicas Chapalita',
                'specialty' => 'Odontología General',
                'city' => 'Zapopan',
                'address' => 'Av. Tepeyac 1287 int. 107, Chapalita Sur, 44040 Zapopan',
                'phone' => '3336305053',
                'has_whatsapp' => false,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Chapalita Sur","source_channel":"directorio-consultoriosdentales","notes":"Torre de especialidades; ortodoncia y estética"}',
            ],
            [
                'name' => 'Dr. César Augusto Bernal Rico',
                'clinic_name' => 'Cabdental Providencia',
                'specialty' => 'Odontología General',
                'city' => 'Guadalajara',
                'address' => 'Av. Terranova 563, Providencia, Guadalajara',
                'phone' => '3336406313',
                'has_whatsapp' => true,
                'email' => 'info@cabdental.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Providencia","instagram":"@cabdental","website":"https:\/\/cabdental.com","source_channel":"google-search","notes":"Borderline: 2 sucursales (Providencia + Sur); consultorio digital"}',
            ],
            [
                'name' => 'Dra. Mónica García Padilla',
                'clinic_name' => 'Odontopediatra Mónica García',
                'specialty' => 'Odontopediatría',
                'city' => 'Guadalajara',
                'address' => 'C. Ottawa 1568 local L6, Providencia, 44630 Guadalajara',
                'phone' => '3315163193',
                'has_whatsapp' => true,
                'email' => 'info@odontopediatraguadalajara.mx',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Providencia","instagram":"@odontopediatria_tips","website":"https:\/\/odontopediatraguadalajara.com\/","source_channel":"google-search","notes":"1 silla; UAG; 15 años experiencia; ICP perfecto"}',
            ],
            [
                'name' => 'OrtoGdl Ortodoncia y Odont. Pediátrica',
                'clinic_name' => 'OrtoGdl Ortodoncia y Odont. Pediátrica',
                'specialty' => 'Ortodoncia',
                'city' => 'Guadalajara',
                'address' => 'Av. Miguel López de Legaspi 2336, Jardines de la Cruz, 44950 Guadalajara',
                'phone' => '3311702331',
                'has_whatsapp' => true,
                'email' => 'info@ortogdl.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Jardines de la Cruz","instagram":"@ortogdl","website":"https:\/\/www.ortogdl.com\/","source_channel":"google-search","notes":"Ortodoncia + odontopediatría; atención a niños con capacidades diferentes"}',
            ],
            [
                'name' => 'Dra. Giovanna Orozco Alpizar',
                'clinic_name' => 'Dental Kid (Dra. Giovanna Orozco)',
                'specialty' => 'Odontopediatría',
                'city' => 'Guadalajara',
                'address' => 'Firmamento 570, Jardines del Bosque, 44520 Guadalajara',
                'phone' => '3311312280',
                'has_whatsapp' => true,
                'email' => 'citas@dentistamx.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Jardines del Bosque","instagram":"@dra_giovanna_orozco","website":"https:\/\/dentistamx.com\/","source_channel":"google-search","notes":"1 doctora titular; odontopediatría + ortodoncia"}',
            ],
            [
                'name' => 'Dr. César Israel Camile Frías',
                'clinic_name' => 'Dentocek',
                'specialty' => 'Implantología',
                'city' => 'Zapopan',
                'address' => 'Santa Catalina de Siena 485, Zapopan',
                'phone' => '3327948523',
                'has_whatsapp' => false,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Zapopan","website":"https:\/\/www.dentocek.com\/","source_channel":"google-search","notes":"2 consultorios cerca (Sta. Catalina + Federico Ibarra); implantología"}',
            ],
            [
                'name' => 'Dr. Miguel Medina Sánchez',
                'clinic_name' => 'Impladent',
                'specialty' => 'Implantología',
                'city' => 'Guadalajara',
                'address' => 'Av. México 2292-A, Ladrón de Guevara, 44600 Guadalajara',
                'phone' => '3313774224',
                'has_whatsapp' => true,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Ladrón de Guevara","website":"https:\/\/impladent.com.mx\/","source_channel":"google-search","notes":"1 sede; implantes carga inmediata"}',
            ],
            [
                'name' => 'Dra. Verónica Ruiz',
                'clinic_name' => 'Dra. Verónica Ruiz Ortodoncia',
                'specialty' => 'Ortodoncia',
                'city' => 'Guadalajara',
                'address' => 'Av. Montevideo 2495, Providencia 3ra sección, 44630',
                'phone' => '3336412101',
                'has_whatsapp' => true,
                'email' => 'clinica@draveronicaruiz.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Providencia 3ra Sección","instagram":"@dra.veronicaruiz","website":"https:\/\/draveronicaruiz.com\/","source_channel":"google-search","notes":"Ortodoncia Invisalign; 1 doctora titular; ICP perfecto"}',
            ],
            [
                'name' => 'Dr. Guillermo Pomar Cotter',
                'clinic_name' => 'Dr. Guillermo Pomar - Natura Torre Médica',
                'specialty' => 'Ortodoncia',
                'city' => 'Zapopan',
                'address' => 'Av. de los Abedules 539, Zapopan',
                'phone' => '3338133616',
                'has_whatsapp' => true,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Zapopan (Abedules)","website":"https:\/\/invisalign-gdl.com\/","source_channel":"google-search","notes":"Ortodoncia sin brackets \/ Invisalign; consultorio privado"}',
            ],
            [
                'name' => 'Dra. Selene Elizondo',
                'clinic_name' => 'Dental Glow GDL',
                'specialty' => 'Odontología Estética',
                'city' => 'Guadalajara',
                'address' => 'José Guadalupe Montenegro 2072, Americana, 44150 Guadalajara',
                'phone' => '3346976630',
                'has_whatsapp' => true,
                'email' => 'dental.glow.gdl@gmail.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Americana","instagram":"@dental_glow_gdl","source_channel":"google-search","notes":"1 doctora titular; estética + diseño de sonrisa"}',
            ],
            [
                'name' => 'Dra. María del Carmen Pérez Ramírez',
                'clinic_name' => 'Consultorio Arcos (Vibliorio)',
                'specialty' => 'Odontología General',
                'city' => 'Zapopan',
                'address' => 'Arco Vespaciano 1114, Arcos de Zapopan 1ra Sección, 45130',
                'phone' => '3336561823',
                'has_whatsapp' => true,
                'email' => 'carmen5602@yahoo.com.mx',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Arcos de Zapopan","website":"https:\/\/www.dentistvibliorio.com.mx\/","source_channel":"google-search","notes":"Tiene 3 micro-consultorios (Arcos\/Calandrias\/Sta Lucía); familiar"}',
            ],
            [
                'name' => 'Dr. Ernesto Silva Damian',
                'clinic_name' => 'Dentaler',
                'specialty' => 'Odontología Estética',
                'city' => 'Zapopan',
                'address' => 'Av. Tepeyac 4168, Ciudad de los Niños, 45040 Zapopan',
                'phone' => '3323037736',
                'has_whatsapp' => true,
                'email' => 'contacto@dentaler.com.mx',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Ciudad de los Niños","instagram":"@dentaler_mx","website":"https:\/\/www.dentaler.com.mx","source_channel":"google-search","notes":"Borderline: 12 profesionales — clínica mediana; 1 sede"}',
            ],
            [
                'name' => 'Dra. Mónica Yazmin Sánchez Cholico',
                'clinic_name' => 'MF Dental Center',
                'specialty' => 'Odontología General',
                'city' => 'Zapopan',
                'address' => '28 de Enero 93 Planta Alta, Zapopan Centro, 45100',
                'phone' => '3310719742',
                'has_whatsapp' => true,
                'email' => 'contacto@mfdentalcenter.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Zapopan Centro","instagram":"@mfdentalcenter","website":"https:\/\/mfdentalcenter.com\/","source_channel":"google-search","notes":"2 doctores; 1 sede; rehab oral + implantes + ortodoncia"}',
            ],
            [
                'name' => 'Clínica Dental Leend',
                'clinic_name' => 'Clínica Dental Leend',
                'specialty' => 'Odontología General',
                'city' => 'Guadalajara',
                'address' => 'Calle Quebec 631, Prados Providencia, 44670 Guadalajara',
                'phone' => '3332192002',
                'has_whatsapp' => true,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Prados Providencia","instagram":"@clinica_dental_leend","website":"https:\/\/www.leend.com.mx\/","source_channel":"google-search","notes":"Borderline: 3 sedes (Obsidiana, Providencia, Valle Real)"}',
            ],
            [
                'name' => 'Dr. Oscar Raygoza',
                'clinic_name' => 'DEO Clinic',
                'specialty' => 'Odontología Estética',
                'city' => 'Guadalajara',
                'address' => 'Av. Guadalupe 618, Chapalita, 44500 Guadalajara',
                'phone' => '3333669038',
                'has_whatsapp' => true,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Chapalita","instagram":"@deoclinic_mx","website":"https:\/\/en.deoclinic.com\/","source_channel":"google-search","notes":"1 doctor titular; estética + general"}',
            ],
            [
                'name' => 'Dra. Amparo Peña Martín del Campo',
                'clinic_name' => 'Dra. Amparo Peña (Dentistaengdl)',
                'specialty' => 'Odontología General',
                'city' => 'Zapopan',
                'address' => 'Av. de las Rosas 430, Chapalita Oriente, 45040 Zapopan',
                'phone' => '3331222941',
                'has_whatsapp' => true,
                'email' => 'dra_amparopena@hotmail.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Chapalita Oriente","instagram":"@dra_amparopena","website":"https:\/\/www.dentistaengdl.com\/","source_channel":"google-search","notes":"30+ años; UAG; consultorio familiar 1 doctora; ICP perfecto"}',
            ],
            [
                'name' => 'Dr. Eduardo Topete Arámbula',
                'clinic_name' => 'Clínica Dr. Eduardo Topete',
                'specialty' => 'Implantología',
                'city' => 'Guadalajara',
                'address' => 'Av. Justo Sierra 2450, Ladrón de Guevara, 44600 Guadalajara',
                'phone' => '3336302574',
                'has_whatsapp' => true,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Ladrón de Guevara","website":"https:\/\/implantesdentalestopete.com\/","source_channel":"google-search","notes":"Familia Topete — 4 doctores mismo consultorio; implantología"}',
            ],
            [
                'name' => 'Dra. Linda Argote Salazar',
                'clinic_name' => 'Dental City by Dra. Linda Argote',
                'specialty' => 'Ortodoncia',
                'city' => 'Zapopan',
                'address' => 'Av. Santa Margarita 4410, Jardín Real, 45136 Zapopan',
                'phone' => '3338323296',
                'has_whatsapp' => false,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Jardín Real (Valle Real)","instagram":"@dentalcity_oficial","website":"https:\/\/dentalcity.mx\/","source_channel":"google-search","notes":"1 doctora titular + equipo; Invisalign + diseño sonrisa digital"}',
            ],
            [
                'name' => 'Punto Dental',
                'clinic_name' => 'Punto Dental',
                'specialty' => 'Ortodoncia',
                'city' => 'Guadalajara',
                'address' => 'Andrés Cavo 452, Jardines de los Arcos, 44500 Guadalajara',
                'phone' => '3396274631',
                'has_whatsapp' => true,
                'email' => 'citas@puntodental.mx',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Jardines de los Arcos","instagram":"@puntodentalmx","website":"https:\/\/puntodental.mx\/","source_channel":"google-search","notes":"Ortodoncia especializada + integral; 1 sede"}',
            ],
            [
                'name' => 'Dra. Melina Morales',
                'clinic_name' => 'Consultorio Dra. Melina Morales',
                'specialty' => 'Endodoncia',
                'city' => 'Zapopan',
                'address' => 'Blvd. del Rodeo 220, El Vigía, Zapopan',
                'phone' => '3338348961',
                'has_whatsapp' => false,
                'email' => 'drameli@academs.mx',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"El Vigía","website":"https:\/\/drameli.academs.mx\/","source_channel":"google-search","notes":"1 doctora titular; odontología integral + endodoncia"}',
            ],
            [
                'name' => 'Clínica Dental Virreyes',
                'clinic_name' => 'Clínica Dental Virreyes',
                'specialty' => 'Odontología General',
                'city' => 'Zapopan',
                'address' => 'Av. Naciones Unidas 6885 B1, Plaza los Tules, 45110 Zapopan',
                'phone' => '3338031957',
                'has_whatsapp' => true,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Vistas del Tule (Virreyes)","website":"https:\/\/www.dentalvirreyes.com.mx\/","source_channel":"google-search","notes":"Ortodoncia + odontopediatría + endodoncia + periodoncia; 1 sede"}',
            ],
            [
                'name' => 'Dra. Paulina Domínguez Solís',
                'clinic_name' => 'Dra. Paulina Domínguez - Cirugía Maxilofacial',
                'specialty' => 'Implantología',
                'city' => 'Guadalajara',
                'address' => 'Tarascos 3473, cons. 820, Torre Especialidades Ángeles del Carmen, Monraz, 44560 Guadalajara',
                'phone' => '3310017927',
                'has_whatsapp' => true,
                'email' => 'contacto@drapaulinamaxilofacial.com',
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Monraz","instagram":"@drapaulinamaxilofacial","website":"https:\/\/drapaulinamaxilofacial.com\/","source_channel":"google-search","notes":"Cirugía oral y maxilofacial \/ implantes; 1 doctora titular; 2a sede Chapala"}',
            ],
            [
                'name' => 'Dental Valle Real',
                'clinic_name' => 'Dental Valle Real',
                'specialty' => 'Odontología General',
                'city' => 'Zapopan',
                'address' => 'Av. Santa Margarita 4750-9, Plaza Casandra, Valle Real, 45140 Zapopan',
                'phone' => '3335856263',
                'has_whatsapp' => false,
                'email' => null,
                'notes' => '{"batch":"research-dentistas-gdl-dedup-20260422","colonia":"Jardín Real (Valle Real)","source_channel":"directorio-guiamexico","notes":"Implantes + pediátrica + ortodoncia + endo\/perio; sin sitio propio verificado"}',
            ],
        ];

        $this->command->info('Insertando ' . count($prospects) . ' dentistas CDMX (pilot)...');

        $inserted = 0;
        $skipped = 0;

        foreach ($prospects as $prospect) {
            // Idempotencia por phone (último match) — no volver a insertar si ya existe
            $existing = Prospect::where('phone', $prospect['phone'])->first();
            if ($existing) {
                $skipped++;
                continue;
            }

            Prospect::create(array_merge($prospect, [
                'source' => 'prospecting', // matches SendProspectEmails cron filter
                'status' => 'new',
                'assigned_to_sales_rep_id' => 100,
                'contact_day' => 0,
                'next_contact_at' => now()->addHour(),
                'outreach_started_at' => now(),
            ]));
            $inserted++;
        }

        $this->command->info("✓ {$inserted} insertados | {$skipped} duplicados saltados | Total en seeder: " . count($prospects));
    }
}
