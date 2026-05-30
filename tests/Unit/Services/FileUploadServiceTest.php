<?php

namespace Tests\Unit\Services;

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 6: Unique filename generation
 *
 * For any two file uploads (even with identical original filenames), the
 * generated storage paths SHALL be distinct.
 *
 * Validates: Requirements 4.4
 */
class FileUploadServiceTest extends TestCase
{
    private FileUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->service = new FileUploadService();
    }

    /**
     * Property 6: Unique filename generation (resumes).
     *
     * Stress the collision case by uploading MANY files that share the exact
     * same original filename and userId. Every returned storage path must be
     * distinct, the stored file must exist on disk, and the path must live
     * under the resumes/ directory.
     *
     * **Validates: Requirements 4.4**
     */
    public function test_resume_uploads_always_produce_unique_paths(): void
    {
        $iterations = 150;
        $userId = 1;
        $paths = [];

        for ($i = 0; $i < $iterations; $i++) {
            $file = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');

            $path = $this->service->uploadResume($file, $userId);

            $this->assertStringStartsWith('resumes/', $path);
            $this->assertTrue(
                Storage::disk('local')->exists($path),
                "Stored resume should exist on the faked disk: {$path}"
            );

            $paths[] = $path;
        }

        $this->assertCount($iterations, $paths);
        $this->assertCount(
            count($paths),
            array_unique($paths),
            'All generated resume storage paths must be distinct.'
        );
    }

    /**
     * Property 6: Unique filename generation (avatars).
     *
     * Same collision stress test for avatar uploads sharing an identical
     * original filename and userId.
     *
     * **Validates: Requirements 4.4**
     */
    public function test_avatar_uploads_always_produce_unique_paths(): void
    {
        $iterations = 150;
        $userId = 1;
        $paths = [];

        for ($i = 0; $i < $iterations; $i++) {
            $file = UploadedFile::fake()->image('avatar.png');

            $path = $this->service->uploadAvatar($file, $userId);

            $this->assertStringStartsWith('avatars/', $path);
            $this->assertTrue(
                Storage::disk('local')->exists($path),
                "Stored avatar should exist on the faked disk: {$path}"
            );

            $paths[] = $path;
        }

        $this->assertCount($iterations, $paths);
        $this->assertCount(
            count($paths),
            array_unique($paths),
            'All generated avatar storage paths must be distinct.'
        );
    }

    /**
     * Property 6: Unique filename generation (mixed uploads, varied users).
     *
     * Combine resume and avatar uploads across a small set of users with
     * repeated identical original filenames. The full set of returned paths
     * (across both directories) must still be globally distinct.
     *
     * **Validates: Requirements 4.4**
     */
    public function test_mixed_uploads_across_users_produce_globally_unique_paths(): void
    {
        $iterations = 100;
        $userIds = [1, 2, 3];
        $paths = [];

        for ($i = 0; $i < $iterations; $i++) {
            $userId = $userIds[$i % count($userIds)];

            $resume = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');
            $avatar = UploadedFile::fake()->image('avatar.png');

            $resumePath = $this->service->uploadResume($resume, $userId);
            $avatarPath = $this->service->uploadAvatar($avatar, $userId);

            $this->assertStringStartsWith('resumes/', $resumePath);
            $this->assertStringStartsWith('avatars/', $avatarPath);
            $this->assertTrue(Storage::disk('local')->exists($resumePath));
            $this->assertTrue(Storage::disk('local')->exists($avatarPath));

            $paths[] = $resumePath;
            $paths[] = $avatarPath;
        }

        $this->assertCount(
            count($paths),
            array_unique($paths),
            'All generated storage paths across resumes and avatars must be distinct.'
        );
    }

    /**
     * Unit example: two consecutive identical uploads never collide.
     */
    public function test_two_identical_resume_uploads_do_not_collide(): void
    {
        $first = $this->service->uploadResume(
            UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            1
        );
        $second = $this->service->uploadResume(
            UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            1
        );

        $this->assertNotSame($first, $second);
        $this->assertTrue(Storage::disk('local')->exists($first));
        $this->assertTrue(Storage::disk('local')->exists($second));
    }
}
