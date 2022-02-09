<?php

namespace App\Models;

use App\Models\Traits\TasksTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTask extends Model
{
    use HasFactory, TasksTrait;

    public $timestamps = false;

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

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
