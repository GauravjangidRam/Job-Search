<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    private const DISK = 'local';
    private const RESUME_DIRECTORY = 'resumes';
    private const AVATAR_DIRECTORY = 'avatars';
    
    /**
     * Resume local disk pe store hoga (private, download ke liye)
     */
    public function uploadResume(UploadedFile $file, int $userId): string
    {
        $filename = $this->buildResumeFilename($file, $userId);
        return Storage::disk(self::DISK)->putFileAs(self::RESUME_DIRECTORY, $file, $filename);
    }

    public function uploadAvatar(UploadedFile $file, int $userId): string
    {
        $filename = $this->buildAvatarFilename($file, $userId);
        return Storage::disk(self::DISK)->putFileAs(self::AVATAR_DIRECTORY, $file, $filename);
    }

    private function buildResumeFilename(UploadedFile $file, int $userId): string
    {
        $sanitizedOriginalName = $this->sanitizeOriginalName($file);
        return sprintf('%d_%d_%s_%s', $userId, now()->getTimestamp(), Str::random(8), $sanitizedOriginalName);
    }

    private function buildAvatarFilename(UploadedFile $file, int $userId): string
    {
        $sanitizedOriginalName = $this->sanitizeOriginalName($file);
        return sprintf('%d_%d_%s_%s', $userId, now()->getTimestamp(), Str::random(8), $sanitizedOriginalName);
    }

    private function sanitizeOriginalName(UploadedFile $file): string
    {
        $originalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $sanitizedBase = preg_replace('/[^A-Za-z0-9._-]+/', '_', $baseName);
        $sanitizedBase = trim((string) $sanitizedBase, '_.');
        if ($sanitizedBase === '') $sanitizedBase = 'file';
        $extension = $this->sanitizeExtension($file);
        return $extension === '' ? $sanitizedBase : $sanitizedBase . '.' . $extension;
    }

    private function sanitizeExtension(UploadedFile $file): string
    {
        return strtolower((string) preg_replace('/[^A-Za-z0-9]/', '', $file->getClientOriginalExtension()));
    }
}
