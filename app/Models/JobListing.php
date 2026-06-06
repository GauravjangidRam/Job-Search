<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Hashids\Hashids;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobListing extends Model
{
    protected $fillable = [
        'title',
        'company_name',
        'company_logo_url',
        'location',
        'salary_min',
        'salary_max',
        'job_type',
        'location_type',
        'description',
        'skills',
        'company_id',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
        ];
    }

    /**
     * Get the company that owns the job listing.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the applications for the job listing.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get the bookmarks for the job listing.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Scope a query to only include active job listings.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getHashedIdAttribute(): string
    {
        $hashids = new Hashids(config('hashids.key'), 8);
        return $hashids->encode($this->id);
    }

    public static function findByHash(string $hash): ?self
    {
        $hashids = new Hashids(config('hashids.key'), 8);
        $decoded = $hashids->decode($hash);
        return isset($decoded[0]) ? self::find($decoded[0]) : null;
    }
}
