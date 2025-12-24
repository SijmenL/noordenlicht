<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function hasTicketsAvailable(int $quantity = 1): bool
    {
        // If max_tickets is null, we assume unlimited tickets
        if (is_null($this->max_tickets)) {
            return true;
        }

        // Explicitly check if max_tickets is 0 to ensure it returns false
        if ($this->max_tickets === 0) {
            return false;
        }

        // Count actual records in the tickets table
        $soldTickets = $this->ticketsSold();

        return ($soldTickets + $quantity) <= $this->max_tickets;
    }

    public function ticketsLeft()
    {
        // If max_tickets is null, we assume unlimited tickets
        if (is_null($this->max_tickets)) {
            return null;
        }

        $soldTickets = $this->ticketsSold();

        return $this->max_tickets - $soldTickets;
    }

    public function ticketsSold()
    {
        $soldTickets = $this->tickets()->count();

        return $soldTickets;
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
        // Assuming you are using the pivot table 'product_prices' created earlier
        // If you are using a polymorphic relationship, adjust accordingly.
        return $this->hasMany(ActivityPrice::class);
    }

}
