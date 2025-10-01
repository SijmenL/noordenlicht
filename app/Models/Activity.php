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
        'end_recurrence'
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

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

}
