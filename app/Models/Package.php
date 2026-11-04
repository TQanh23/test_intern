<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Package extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'level',
        'price',
        'cover_image',
        'description',
    ];

    /**
     * @return HasMany<Unit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Tài liệu đi qua unit, để withCount('documents') đếm bằng một subquery.
     *
     * @return HasManyThrough<Document, Unit, $this>
     */
    public function documents(): HasManyThrough
    {
        return $this->hasManyThrough(Document::class, Unit::class);
    }
}
