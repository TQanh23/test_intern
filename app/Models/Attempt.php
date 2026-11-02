<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Attempt extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'attempts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'exam_id',
        'exam_name',
        'duration_minutes',
        'status',
        'started_at',
        'deadline_at',
        'submitted_at',
        'score',
        'total',
        'questions',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * Không cast `questions` sang array: để giữ mảng BSON thật cho $unwind ở task 14.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'deadline_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }
}
