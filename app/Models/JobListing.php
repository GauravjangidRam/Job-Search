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
        'currency',
        'job_type',
        'location_type',
        'description',
        'skills',
        'company_id',
        'status',
    ];

    /**
     * Attribute casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'skills' => 'array',
        'salary_min' => 'integer',
        'salary_max' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
        'hashed_id',
    ];

    /**
     * Get the currency symbol.
     */
    public function getCurrencySymbolAttribute(): string
    {
        return $this->currency === 'USD' ? '$' : '₹';
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
        $hashids = new Hashids(config('app.key'), 8);
        return $hashids->encode($this->id);
    }

    public function getUrlAttribute(): string
    {
        return route('jobs.show', [
            'hash' => $this->hashed_id,
            'slug' => \Illuminate\Support\Str::slug($this->title),
        ]);
    }

    public static function findByHash(string $hash): ?self
    {
        $hashids = new Hashids(config('app.key'), 8);
        $decoded = $hashids->decode($hash);
        return isset($decoded[0]) ? self::find($decoded[0]) : null;
    }
}
