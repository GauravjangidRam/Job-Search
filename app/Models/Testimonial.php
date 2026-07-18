<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Testimonial extends Model
{
    protected $fillable = [
        'name',
        'role',
        'company',
        'avatar_url',
        'text',
        'rating',
        'is_featured',
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Scope the query to explicitly featured testimonials.
     *
     * `IS TRUE` is portable across PostgreSQL, MySQL, and SQLite and avoids
     * binding PHP booleans as integer values on PostgreSQL connections.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->whereRaw('is_featured IS TRUE');
    }
}
