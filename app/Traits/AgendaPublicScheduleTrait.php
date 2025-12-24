<?php

namespace App\Traits;

use App\Models\Activity;
use App\Models\ActivityException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

trait AgendaPublicScheduleTrait
{
    protected function fetchAndProcessActivities(int $monthOffset = 0, ?int $limit = null): \Illuminate\Support\Collection
    {
        Carbon::setLocale('nl');
        $baseDate = Carbon::now();
        $calculatedDate = $baseDate->copy()->addMonths($monthOffset);

// Use a 3-month display period.
        $rangeStart = $calculatedDate->copy()->startOfDay();
        $rangeEnd = $calculatedDate->copy()->addMonths(3)->endOfMonth()->endOfDay();

        $fetchedActivities = Activity::query()
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('date_start', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($q) use ($rangeStart, $rangeEnd) {
                        $q->whereIn('recurrence_rule', ['daily', 'weekly', 'monthly'])
                            ->where(function ($q2) use ($rangeStart) {
                                $q2->whereNull('end_recurrence')
                                    ->orWhere('end_recurrence', '>=', $rangeStart);
                            })
                            ->where('date_start', '<=', $rangeEnd);
                    });
            })
            ->orderBy('date_start')
            ->get();


        // Load exceptions for single-instance deletions
        $exceptionsByActivity = ActivityException::whereIn('activity_id', $fetchedActivities->pluck('id'))
            ->get()
            ->groupBy('activity_id')
            ->map(function ($group) {
                return $group->pluck('date')
                    ->map(fn($d) => \Illuminate\Support\Carbon::parse($d)->toDateString())
                    ->toArray();
            });

        // Expand recurring events
        $activities = collect();
        foreach ($fetchedActivities as $activity) {
            $occurrences = $this->expandRecurringActivity($activity, $rangeStart, $rangeEnd);
            $skipDates = $exceptionsByActivity[$activity->id] ?? [];

            foreach ($occurrences as $occurrence) {
                $occDate = Carbon::parse($occurrence->date_start)->toDateString();

                // Skip single-instance exceptions
                if (in_array($occDate, $skipDates, true)) {
                    continue;
                }

                // Skip beyond end_recurrence
                if (!is_null($activity->end_recurrence)) {
                    $endRec = Carbon::parse($activity->end_recurrence)->endOfDay();
                    if (Carbon::parse($occurrence->date_start)->gt($endRec)) {
                        continue;
                    }
                }


                $activities->push($occurrence);
            }
        }

        $activities = $activities->sortBy('date_start')->values();

        return $activities->take($limit);
    }


    protected function getAgendaViewData(int $monthOffset = 0, ?int $limit = null): array
    {
        Carbon::setLocale('nl');
        $baseDate = Carbon::now();
        $calculatedDate = $baseDate->copy()->addMonths($monthOffset);
        $monthName = $calculatedDate->translatedFormat('F');
        $calculatedYear = $calculatedDate->year;

        return [
            'monthOffset' => $monthOffset,
            'monthName' => $monthName,
            'year' => $calculatedYear,
            'limit' => $limit,
        ];
    }

    protected function expandRecurringActivity($activity, $rangeStart, $rangeEnd)
    {
        $occurrences = [];

        if (empty($activity->recurrence_rule) || !in_array($activity->recurrence_rule, ['daily', 'weekly', 'monthly'])) {
            return [$activity];
        }

        $originalStart = \Illuminate\Support\Carbon::parse($activity->date_start);
        $originalEnd = Carbon::parse($activity->date_end);
        $duration = $originalEnd->diffInSeconds($originalStart);

        $lastOccurrence = $activity->end_recurrence ? Carbon::parse($activity->end_recurrence)->endOfDay() : Carbon::maxValue();

        $currentOccurrenceStart = $originalStart->copy();

        while ($currentOccurrenceStart->lte($rangeEnd) && $currentOccurrenceStart->lte($lastOccurrence)) {
            $currentOccurrenceEnd = $currentOccurrenceStart->copy()->addSeconds($duration);

            if ($currentOccurrenceStart->between($rangeStart, $rangeEnd)) {
                $clone = clone $activity;
                $clone->date_start = $currentOccurrenceStart->copy();
                $clone->date_end = $currentOccurrenceEnd->copy();
                $occurrences[] = $clone;
            }

            switch ($activity->recurrence_rule) {
                case 'daily':
                    $currentOccurrenceStart->addDay();
                    break;
                case 'weekly':
                    $currentOccurrenceStart->addWeek();
                    break;
                case 'monthly':
                    $currentOccurrenceStart->addMonth();
                    break;
            }
        }

        return $occurrences;
    }
}
