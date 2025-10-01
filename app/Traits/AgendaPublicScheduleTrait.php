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
        $rangeStart = $calculatedDate->copy()->startOfMonth()->startOfDay();
        $rangeEnd = $calculatedDate->copy()->addMonths(3)->endOfMonth()->endOfDay();

        $query = Activity::where(function ($q) use ($rangeStart, $rangeEnd) {
            $q->whereBetween('date_start', [$rangeStart, $rangeEnd])
                ->orWhere(function ($q2) use ($rangeStart, $rangeEnd) {
                    $q2->whereIn('recurrence_rule', ['daily', 'weekly', 'monthly'])
                        ->where(function ($q3) use ($rangeStart) {
                            $q3->whereNull('end_recurrence')
                                ->orWhere('end_recurrence', '>=', $rangeStart);
                        })
                        ->where('date_start', '<=', $rangeEnd);
                });
        })
            ->where('public', true)
            ->orderBy('date_start');

        if ($limit !== null) {
            $query->limit($limit);
        }
        $fetchedActivities = $query->get();

// Load exceptions for single-instance deletions
        $exceptionsByActivity = ActivityException::whereIn('activity_id', $fetchedActivities->pluck('id'))
            ->get()
            ->groupBy('activity_id')
            ->map(function ($group) {
                return $group->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();
            });


// Expand recurring events—even if the original date is far in the past.

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

// Skip occurrences after end_recurrence if set
                if (!is_null($activity->end_recurrence)) {
                    $endRec = Carbon::parse($activity->end_recurrence)->endOfDay();
                    if (Carbon::parse($occurrence->date_start)->gt($endRec)) {
                        continue;
                    }
                }

                if ($activity->recurrence_rule === 'none') {
                    $requestedDate = Carbon::now();
                    if ($requestedDate && $occDate !== Carbon::parse($requestedDate)->toDateString()) {
                        continue;
                    }
                }
                $activities->push($occurrence);
            }
        }
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
