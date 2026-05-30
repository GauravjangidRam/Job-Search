<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'website_url',
        'description',
        'culture',
        'employee_count',
        'founded_year',
        'industry',
        'is_hiring',
        'metrics',
        'perks',
        'verification_status',
        'verified_at',
        'rejection_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'perks' => 'array',
            'is_hiring' => 'boolean',
            'employee_count' => 'integer',
            'founded_year' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Get the job listings for the company.
     */
    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }

    /**
     * Get the employer users that belong to the company.
     */
    public function employers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'employer');
    }

    /**
     * Boot the model and register the creating event for slug auto-generation.
     */
    protected static function booted(): void
    {
        static::creating(function (Company $company) {
            $company->slug = $company->slug ?? static::generateUniqueSlug($company->name);
        });
    }

    /**
     * Check if the company is verified/approved by admin.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'approved';
    }

    /**
     * Check if the company is pending verification.
     */
    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    /**
     * Generate a unique slug from the given name.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }
}
