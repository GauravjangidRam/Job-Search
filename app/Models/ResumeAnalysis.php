<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeAnalysis extends Model
{
    protected $table = 'resume_analyses';

    protected $fillable = [
        'job_application_id',
        'resume_path',
        'analysis',
        'provider',
    ];

    protected $casts = [
        'analysis' => 'array',
    ];

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
