<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityFormResponses extends Model
{
    // Added 'order_id'
    protected $fillable = [
        'activity_id',
        'product_id',
        'order_id',
        'location',
        'activity_form_element_id',
        'response',
        'submitted_id'
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // New relationship for Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function activityFormElement()
    {
        return $this->belongsTo(ActivityFormElement::class);
    }

    public function formElement()
    {
        return $this->belongsTo(ActivityFormElement::class, 'activity_form_element_id');
    }
}
