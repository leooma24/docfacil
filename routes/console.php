<?php

use Illuminate\Support\Facades\Schedule;

// Send trial/beta expiring emails daily at 9am
Schedule::command('docfacil:send-trial-emails')->dailyAt('09:00');

// Send engagement emails daily at 10am
Schedule::command('docfacil:send-engagement')->dailyAt('10:00');

// Send appointment reminders every hour (handles 24h, 2h, and follow-ups)
// NOTA: solo envia automaticamente via WhatsApp API global de DocFacil
// (config/services.php:whatsapp). En el setup actual esto es solo para demo/
// testing — los widgets del Doctor panel usan click-to-wa.me manual para que
// cada clinica use su propio WhatsApp personal sin costar API calls.
Schedule::command('docfacil:send-reminders')->hourly()->withoutOverlapping();

// Cumpleanos: el comando existe para cuando una clinica conecte su propia
// WA Business API. Por default NO se programa — los doctores ven los
// cumpleanos de hoy en el widget BirthdaysToday con click-to-wa.me.
// Para activar: descomentar la siguiente linea.
// Schedule::command('docfacil:send-birthday-greetings')->dailyAt('10:00');

// Send prospect pipeline emails every hour (max 10 per run)
Schedule::command('docfacil:send-prospect-emails')->hourly();

// Recalcular lead_score de prospects cada noche para reflejar
// engagement reciente (clicks de correos) y decay automático.
Schedule::command('docfacil:recalculate-lead-scores')->dailyAt('02:30');

// SPEI: recordatorio 5 días antes del vencimiento del plan (solo método SPEI; Stripe se renueva solo)
Schedule::command('docfacil:send-spei-reminders')->dailyAt('09:30');

// Marketplace: cancela compras pending_payment > 24 hrs sin completar
Schedule::command('docfacil:cleanup-stale-premium-purchases')->dailyAt('03:30');

// Backups daily at 3am
Schedule::command('backup:run')->dailyAt('03:00');

// Reset demo clinic daily at 4am (lets visitors create/edit freely during the day)
Schedule::command('app:demo-reset')->dailyAt('04:00');

// Auditoría de retención legal (LFPDPPP + NOM-004): reporte semanal,
// solo informativo. El borrado efectivo se hace manualmente con --force.
Schedule::command('app:retention-report')->weekly()->sundays()->at('05:00');
