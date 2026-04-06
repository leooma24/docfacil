<?php

use Illuminate\Support\Facades\Schedule;

// Send trial/beta expiring emails daily at 9am
Schedule::command('docfacil:send-trial-emails')->dailyAt('09:00');

// Send engagement emails daily at 10am
Schedule::command('docfacil:send-engagement')->dailyAt('10:00');

// Send appointment reminders daily at 8am
Schedule::command('docfacil:send-reminders')->dailyAt('08:00');

// Backups daily at 3am
Schedule::command('backup:run')->dailyAt('03:00');
