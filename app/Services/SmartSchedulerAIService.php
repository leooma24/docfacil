<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class SmartSchedulerAIService
{
    /**
     * Find the best available slots for an appointment, considering:
     * - Existing appointments (no conflicts)
     * - 5 min buffer between appointments
     * - Lunch break (13:00-15:00)
     * - Business hours (9:00-19:00 by default)
     * - Preferred spacing (group similar services together)
     */
    public function findBestSlots(
        int $clinicId,
        int $doctorId,
        int $durationMinutes = 30,
        int $daysAhead = 7,
        int $maxSlots = 6
    ): array {
        $existingAppts = Appointment::where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', now()->addDays($daysAhead))
            ->orderBy('starts_at')
            ->get(['starts_at', 'ends_at']);

        $slots = [];
        $buffer = 5; // 5 minute buffer between appointments

        for ($d = 0; $d < $daysAhead && count($slots) < $maxSlots * 2; $d++) {
            $day = now()->addDays($d)->startOfDay();

            // Skip Sundays
            if ($day->dayOfWeek === Carbon::SUNDAY) continue;

            // Business hours: 9am-7pm
            $dayStart = (clone $day)->setHour(9)->setMinute(0);
            $dayEnd = (clone $day)->setHour(19)->setMinute(0);
            $lunchStart = (clone $day)->setHour(13)->setMinute(0);
            $lunchEnd = (clone $day)->setHour(15)->setMinute(0);

            // If today, start from current time rounded up to next 15 min
            if ($d === 0) {
                $now = now();
                $dayStart = $now->copy()->addMinutes(15 - ($now->minute % 15))->setSecond(0);
                if ($dayStart->greaterThan($dayEnd)) continue;
            }

            // Generate candidate slots every 15 min
            $cursor = $dayStart->copy();
            while ($cursor->copy()->addMinutes($durationMinutes)->lessThanOrEqualTo($dayEnd)) {
                $slotEnd = $cursor->copy()->addMinutes($durationMinutes);

                // Skip lunch
                if ($cursor->lessThan($lunchEnd) && $slotEnd->greaterThan($lunchStart)) {
                    $cursor = $lunchEnd->copy();
                    continue;
                }

                // Check conflicts with existing appointments
                $conflicts = $existingAppts->first(function ($a) use ($cursor, $slotEnd, $buffer) {
                    $aStart = Carbon::parse($a->starts_at)->subMinutes($buffer);
                    $aEnd = Carbon::parse($a->ends_at)->addMinutes($buffer);
                    return $cursor->lessThan($aEnd) && $slotEnd->greaterThan($aStart);
                });

                if (!$conflicts) {
                    $slots[] = [
                        'starts_at' => $cursor->toIso8601String(),
                        'ends_at' => $slotEnd->toIso8601String(),
                        'label' => $this->humanLabel($cursor),
                        'day' => $cursor->translatedFormat('l d \d\e F'),
                        'time' => $cursor->format('H:i'),
                        'score' => $this->scoreSlot($cursor, $existingAppts),
                    ];
                }

                $cursor->addMinutes(15);
            }
        }

        // Sort by score (higher = better) and return top N
        usort($slots, fn ($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($slots, 0, $maxSlots);
    }

    protected function humanLabel(Carbon $dt): string
    {
        if ($dt->isToday()) return 'Hoy ' . $dt->format('H:i');
        if ($dt->isTomorrow()) return 'Mañana ' . $dt->format('H:i');
        return $dt->translatedFormat('l j') . ' ' . $dt->format('H:i');
    }

    protected function scoreSlot(Carbon $slot, $existingAppts): int
    {
        $score = 100;

        // Prefer mornings (9-12) slightly
        if ($slot->hour >= 9 && $slot->hour < 12) $score += 10;

        // Prefer next to existing appointments (efficient scheduling)
        foreach ($existingAppts as $a) {
            $aStart = Carbon::parse($a->starts_at);
            $aEnd = Carbon::parse($a->ends_at);
            $diffMinutes = min(
                abs($slot->diffInMinutes($aStart, false)),
                abs($slot->diffInMinutes($aEnd, false))
            );
            if ($diffMinutes < 30) $score += 5;
        }

        // Prefer sooner dates
        $daysAway = $slot->diffInDays(now());
        $score -= $daysAway * 2;

        return $score;
    }
}
