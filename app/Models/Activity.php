<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Activity extends Model
{
    /**
     * @var \Illuminate\Support\HigherOrderCollectionProxy|mixed
     */
    protected $fillable = [
        'content',
        'public',
        'user_id',
        'date_start',
        'date_end',
        'image',
        'title',
        'roles',
        'users',
        'price',
        'location',
        'organisator',
        'repeat',
        'presence',
        'lesson_id',
        'recurrence_rule',
        'end_recurrence',
        'max_tickets'
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Check if tickets are available for a specific occurrence date.
     */
    public function hasTicketsAvailable(int $quantity = 1, $date = null): bool
    {
        // If max_tickets is null, we assume unlimited tickets
        if (is_null($this->max_tickets)) {
            return true;
        }

        // Explicitly check if max_tickets is 0 to ensure it returns false
        if ($this->max_tickets === 0) {
            return false;
        }

        // Count actual records in the tickets table for this specific occurrence
        $soldTickets = $this->ticketsSold($date);

        return ($soldTickets + $quantity) <= $this->max_tickets;
    }

    /**
     * Get the number of tickets left for a specific occurrence date.
     */
    public function ticketsLeft($date = null)
    {
        // If max_tickets is null, we assume unlimited tickets
        if (is_null($this->max_tickets)) {
            return null;
        }

        $soldTickets = $this->ticketsSold($date);

        return $this->max_tickets - $soldTickets;
    }

    /**
     * Count sold tickets, optionally filtered by the occurrence date.
     */
    public function ticketsSold($date = null)
    {
        $query = $this->tickets();

        // 1. If a specific date is passed, filter by it.
        if ($date) {
            $formattedDate = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();
            $query->whereDate('start_date', $formattedDate);
        }
        // 2. If no date is passed, BUT this is a recurring activity, we must scope to the current occurrence.
        // The AgendaController updates $this->date_start to the specific occurrence date before rendering the view.
        elseif ($this->recurrence_rule && $this->recurrence_rule !== 'never') {
            $formattedDate = $this->date_start instanceof Carbon ? $this->date_start->toDateString() : Carbon::parse($this->date_start)->toDateString();
            $query->whereDate('start_date', $formattedDate);
        }
        // 3. If it's a non-recurring event and no date is passed, we simply count all tickets for this ID.
        // This maintains backward compatibility for standard events.

        return $query->count();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function formElements()
    {
        return $this->hasMany(ActivityFormElement::class);
    }

    public function activityFormElements()
    {
        return $this->hasMany(ActivityFormElement::class, 'activity_id');
    }

    public function prices()
    {
        return $this->hasMany(ActivityPrice::class);
    }
}
