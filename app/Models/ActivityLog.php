<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ActivityLog extends Model
{
    // Log chỉ ghi thêm, không bao giờ sửa.
    public const UPDATED_AT = null;

    protected $connection = 'mongodb';

    protected $table = 'activity_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'exam_id',
        'details',
    ];
}
