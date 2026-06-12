<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'otp', 'otp_expires_at', 'otp_attempts', 'role', 'phone', 'bio', 'avatar_path', 'avatar_url', 'google_id', 'company_id'])]
#[Hidden(['password', 'remember_token', 'otp'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the job applications submitted by the user (as a seeker).
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'user_id');
    }

    public function resumeAnalyses(): HasMany
    {
        return $this->hasMany(ResumeAnalysis::class);
    }

    /**
     * Get the bookmarks saved by the user (as a seeker).
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get the company the user belongs to (as an employer).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Determine whether the user has the seeker role.
     */
    public function isSeeker(): bool
    {
        return $this->role === 'seeker';
    }

    /**
     * Determine whether the user has the employer role.
     */
    public function isEmployer(): bool
    {
        return $this->role === 'employer';
    }

    /**
     * Determine whether the user has the admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
