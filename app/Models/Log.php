<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'user_id',
        'display_text',
        'reference',
        'type',
        'action',
        'location'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createLog($userId, $action, $type, $location, $reference, $displayText) {
        $log = new Log();
        $log->user_id = $userId;
        $log->action = $action;
        $log->type = $type;
        $log->location = $location;
        $log->reference = $reference;
        $log->display_text = $displayText;
        $log->save();
    }
}
