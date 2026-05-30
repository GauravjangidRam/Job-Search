<?php

namespace Tests\Unit\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 3: File upload validation
 *
 * For any uploaded file, the validation logic accepts the file if and only if
 * its MIME type is in the allowed set AND its size is at or below the maximum
 * threshold:
 *   - Avatars: JPEG/PNG/WebP, <= 2 MB (2048 KB)
 *   - Resumes: PDF/DOC/DOCX, <= 5 MB (5120 KB)
 *
 * The avatar rules live in ProfileUpdateRequest and the resume rules in
 * JobApplicationRequest. Those form requests do not exist yet, so this test
 * validates the rule logic directly by building a Validator with the exact
 * rule sets the design specifies.
 *
 * Validates: Requirements 2.3, 4.3
 */
class FileValidationPropertyTest extends TestCase
{
    /**
     * Avatar rule set as specified by the design (max in kilobytes).
     */
    private const AVATAR_RULES = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'];

    private const AVATAR_MAX_KB = 2048;

    /**
     * Resume rule set as specified by the design (max in kilobytes).
     */
    private const RESUME_RULES = ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'];

    private const RESUME_MAX_KB = 5120;

    /**
     * Number of randomized iterations per property (design requires >= 100).
     */
    private const ITERATIONS = 200;

    /**
     * Candidate file types mixing allowed and disallowed kinds.
     *
     * Each entry is [extension, mimeType, allowedForAvatar, allowedForResume].
     */
    private function fileTypes(): array
    {
        return [
            // Image types
            ['jpg', 'image/jpeg', true, false],
            ['png', 'image/png', true, false],
            ['webp', 'image/webp', true, false],
            ['gif', 'image/gif', false, false],
            ['bmp', 'image/bmp', false, false],
            // Document types
            ['pdf', 'application/pdf', false, true],
            ['doc', 'application/msword', false, true],
            ['docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', false, true],
            // Clearly-disallowed types
            ['txt', 'text/plain', false, false],
            ['exe', 'application/x-msdownload', false, false],
        ];
    }

    /**
     * Generate a random size (KB) biased toward the threshold boundary while
     * still covering a broad range of below/above-threshold values.
     */
    private function randomSizeKb(int $thresholdKb): int
    {
        $choices = [
            1,
            (int) max(1, $thresholdKb / 2),
            $thresholdKb - 1,
            $thresholdKb,
            $thresholdKb + 1,
            $thresholdKb * 2,
            random_int(1, $thresholdKb * 2 + 50),
        ];

        return $choices[array_rand($choices)];
    }

    public function test_property_avatar_validation_accepts_iff_allowed_type_and_size_within_limit(): void
    {
        $types = $this->fileTypes();

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            [$ext, $mime, $allowedForAvatar] = $types[array_rand($types)];
            $sizeKb = $this->randomSizeKb(self::AVATAR_MAX_KB);

            // UploadedFile::fake()->create() sets the reported size (KB) and
            // MIME type without allocating bytes, so the validator's mimes/max
            // rules see exactly these values.
            $file = UploadedFile::fake()->create("upload.{$ext}", $sizeKb, $mime);

            $expected = $allowedForAvatar && $sizeKb <= self::AVATAR_MAX_KB;

            $validator = Validator::make(['file' => $file], ['file' => self::AVATAR_RULES]);

            $this->assertSame(
                $expected,
                $validator->passes(),
                sprintf(
                    'Avatar validation mismatch for ext=%s mime=%s size=%dKB: expected %s but got %s',
                    $ext,
                    $mime,
                    $sizeKb,
                    $expected ? 'accept' : 'reject',
                    $validator->passes() ? 'accept' : 'reject'
                )
            );
        }
    }

    public function test_property_resume_validation_accepts_iff_allowed_type_and_size_within_limit(): void
    {
        $types = $this->fileTypes();

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            [$ext, $mime, , $allowedForResume] = $types[array_rand($types)];
            $sizeKb = $this->randomSizeKb(self::RESUME_MAX_KB);

            $file = UploadedFile::fake()->create("upload.{$ext}", $sizeKb, $mime);

            $expected = $allowedForResume && $sizeKb <= self::RESUME_MAX_KB;

            $validator = Validator::make(['file' => $file], ['file' => self::RESUME_RULES]);

            $this->assertSame(
                $expected,
                $validator->passes(),
                sprintf(
                    'Resume validation mismatch for ext=%s mime=%s size=%dKB: expected %s but got %s',
                    $ext,
                    $mime,
                    $sizeKb,
                    $expected ? 'accept' : 'reject',
                    $validator->passes() ? 'accept' : 'reject'
                )
            );
        }
    }

    public function test_avatar_boundary_exactly_at_threshold_is_accepted_and_one_over_is_rejected(): void
    {
        $atLimit = UploadedFile::fake()->create('avatar.png', self::AVATAR_MAX_KB, 'image/png');
        $overLimit = UploadedFile::fake()->create('avatar.png', self::AVATAR_MAX_KB + 1, 'image/png');

        $this->assertTrue(
            Validator::make(['file' => $atLimit], ['file' => self::AVATAR_RULES])->passes(),
            'A 2048 KB PNG avatar should be accepted (at the limit).'
        );
        $this->assertFalse(
            Validator::make(['file' => $overLimit], ['file' => self::AVATAR_RULES])->passes(),
            'A 2049 KB PNG avatar should be rejected (over the limit).'
        );
    }

    public function test_resume_boundary_exactly_at_threshold_is_accepted_and_one_over_is_rejected(): void
    {
        $atLimit = UploadedFile::fake()->create('resume.pdf', self::RESUME_MAX_KB, 'application/pdf');
        $overLimit = UploadedFile::fake()->create('resume.pdf', self::RESUME_MAX_KB + 1, 'application/pdf');

        $this->assertTrue(
            Validator::make(['file' => $atLimit], ['file' => self::RESUME_RULES])->passes(),
            'A 5120 KB PDF resume should be accepted (at the limit).'
        );
        $this->assertFalse(
            Validator::make(['file' => $overLimit], ['file' => self::RESUME_RULES])->passes(),
            'A 5121 KB PDF resume should be rejected (over the limit).'
        );
    }

    public function test_genuine_images_are_accepted_for_avatar_within_size_limit(): void
    {
        foreach (['avatar.jpg', 'avatar.png', 'avatar.webp'] as $name) {
            $file = UploadedFile::fake()->image($name, 64, 64);

            $this->assertTrue(
                Validator::make(['file' => $file], ['file' => self::AVATAR_RULES])->passes(),
                "Genuine image {$name} should be accepted as an avatar."
            );
        }
    }

    public function test_avatar_is_nullable_but_resume_is_required(): void
    {
        $this->assertTrue(
            Validator::make(['file' => null], ['file' => self::AVATAR_RULES])->passes(),
            'A null avatar should pass because the rule set is nullable.'
        );

        $this->assertFalse(
            Validator::make([], ['file' => self::RESUME_RULES])->passes(),
            'A missing resume should fail because the rule set is required.'
        );
    }
}
