<?php

use Illuminate\Support\Facades\Schedule;

// Send trial/beta expiring emails daily at 9am
Schedule::command('docfacil:send-trial-emails')->dailyAt('09:00');

// Send engagement emails daily at 10am
Schedule::command('docfacil:send-engagement')->dailyAt('10:00');

// Send appointment reminders every hour (handles 24h, 2h, and follow-ups)
Schedule::command('docfacil:send-reminders')->hourly()->withoutOverlapping();

// Send prospect pipeline emails every hour (max 10 per run)
Schedule::command('docfacil:send-prospect-emails')->hourly();

// SPEI: recordatorio 5 días antes del vencimiento del plan (solo método SPEI; Stripe se renueva solo)
Schedule::command('docfacil:send-spei-reminders')->dailyAt('09:30');

// Backups daily at 3am
Schedule::command('backup:run')->dailyAt('03:00');

// Reset demo clinic daily at 4am (lets visitors create/edit freely during the day)
Schedule::command('app:demo-reset')->dailyAt('04:00');

// Auditoría de retención legal (LFPDPPP + NOM-004): reporte semanal,
// solo informativo. El borrado efectivo se hace manualmente con --force.
Schedule::command('app:retention-report')->weekly()->sundays()->at('05:00');
