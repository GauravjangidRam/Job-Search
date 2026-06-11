<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\ResumeAnalysis;
use Illuminate\Support\Facades\Storage;

class ResumeAnalysisService
{
    /**
     * Perform a lightweight analysis of the uploaded resume and persist it.
     * Currently collects basic metadata (filename, size, mime) and stores as JSON.
     */
    public function analyze(JobApplication $application, string $resumePath): ResumeAnalysis
    {
        $disk = 'local';

        try {
            $size = Storage::disk($disk)->size($resumePath);
        } catch (\Throwable $e) {
            $size = null;
        }

        try {
            $mime = Storage::disk($disk)->mimeType($resumePath);
        } catch (\Throwable $e) {
            $mime = null;
        }

        $analysis = [
            'file_name' => basename($resumePath),
            'size_bytes' => $size,
            'mime_type' => $mime,
            'note' => 'Basic metadata analysis',
        ];

        return ResumeAnalysis::create([
            'job_application_id' => $application->id,
            'resume_path' => $resumePath,
            'analysis' => $analysis,
            'provider' => 'local-metadata',
        ]);
    }
}
