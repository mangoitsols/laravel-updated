<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSpace extends Model
{
    use HasFactory;
     protected $fillable = [
        'user_id',
        'task_id',
        'message',
        'is_completed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }   
}
