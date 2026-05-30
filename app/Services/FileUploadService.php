<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * The filesystem disk used for storing private uploads.
     *
     * The "local" disk maps to storage/app/private in Laravel 11+.
     */
    private const DISK = 'local';

    /**
     * Directory (relative to the disk root) where resume files are stored.
     */
    private const RESUME_DIRECTORY = 'resumes';

    /**
     * Directory (relative to the disk root) where avatar files are stored.
     */
    private const AVATAR_DIRECTORY = 'avatars';

    /**
     * Store an uploaded resume file and return its stored relative path.
     *
     * The generated filename follows the pattern
     * {user_id}_{timestamp}_{entropy}_{sanitized_original_name} so that two
     * uploads of an identically named file never collide, even within the
     * same second.
     */
    public function uploadResume(UploadedFile $file, int $userId): string
    {
        $filename = $this->buildResumeFilename($file, $userId);

        return Storage::disk(self::DISK)->putFileAs(self::RESUME_DIRECTORY, $file, $filename);
    }

    /**
     * Store an uploaded avatar file and return its stored relative path.
     *
     * The generated filename follows the pattern
     * {user_id}_{timestamp}_{entropy}.{ext} so that two uploads never collide,
     * even within the same second.
     */
    public function uploadAvatar(UploadedFile $file, int $userId): string
    {
        $filename = $this->buildAvatarFilename($file, $userId);

        return Storage::disk(self::DISK)->putFileAs(self::AVATAR_DIRECTORY, $file, $filename);
    }

    /**
     * Build a unique, filesystem-safe filename for a resume upload.
     *
     * Preserves the user id and the sanitized original filename (including its
     * extension) while injecting a timestamp and random entropy for uniqueness.
     */
    private function buildResumeFilename(UploadedFile $file, int $userId): string
    {
        $sanitizedOriginalName = $this->sanitizeOriginalName($file);

        return sprintf(
            '%d_%d_%s_%s',
            $userId,
            now()->getTimestamp(),
            Str::random(8),
            $sanitizedOriginalName
        );
    }

    /**
     * Build a unique, filesystem-safe filename for an avatar upload.
     *
     * Preserves the user id and the original file extension while injecting a
     * timestamp and random entropy for uniqueness.
     */
    private function buildAvatarFilename(UploadedFile $file, int $userId): string
    {
        $extension = $this->sanitizeExtension($file);
        $suffix = $extension === '' ? '' : '.'.$extension;

        return sprintf(
            '%d_%d_%s%s',
            $userId,
            now()->getTimestamp(),
            Str::random(8),
            $suffix
        );
    }

    /**
     * Produce a sanitized version of the original filename.
     *
     * Strips any path separators and unsafe characters from the base name while
     * preserving a sanitized extension. Falls back to "file" when the base name
     * sanitizes down to nothing.
     */
    private function sanitizeOriginalName(UploadedFile $file): string
    {
        $originalName = $file->getClientOriginalName();

        // Guard against directory traversal by keeping only the base name.
        $originalName = basename(str_replace('\\', '/', $originalName));

        $baseName = pathinfo($originalName, PATHINFO_FILENAME);

        // Replace any unsafe characters; collapse runs into single underscores.
        $sanitizedBase = preg_replace('/[^A-Za-z0-9._-]+/', '_', $baseName);
        $sanitizedBase = trim((string) $sanitizedBase, '_.');

        if ($sanitizedBase === '') {
            $sanitizedBase = 'file';
        }

        $extension = $this->sanitizeExtension($file);

        return $extension === '' ? $sanitizedBase : $sanitizedBase.'.'.$extension;
    }

    /**
     * Produce a sanitized, lowercase file extension (without a leading dot).
     *
     * Returns an empty string when no usable extension is present.
     */
    private function sanitizeExtension(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        $extension = strtolower((string) preg_replace('/[^A-Za-z0-9]/', '', $extension));

        return $extension;
    }
}
