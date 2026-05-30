<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    private OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->otpService = new OtpService();
    }

    public function test_generate_returns_six_digit_numeric_string(): void
    {
        $user = User::factory()->create();

        $otp = $this->otpService->generate($user);

        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
    }

    public function test_generate_stores_hashed_otp_on_user(): void
    {
        $user = User::factory()->create();

        $otp = $this->otpService->generate($user);

        $user->refresh();
        $this->assertNotNull($user->otp);
        $this->assertNotEquals($otp, $user->otp);
        $this->assertTrue(Hash::check($otp, $user->otp));
    }

    public function test_generate_sets_expiration_to_ten_minutes(): void
    {
        $user = User::factory()->create();

        $this->otpService->generate($user);

        $user->refresh();
        $this->assertNotNull($user->otp_expires_at);
        $this->assertEqualsWithDelta(
            now()->addMinutes(10)->timestamp,
            $user->otp_expires_at->timestamp,
            5
        );
    }

    public function test_generate_resets_otp_attempts_to_zero(): void
    {
        $user = User::factory()->create(['otp_attempts' => 3]);

        $this->otpService->generate($user);

        $user->refresh();
        $this->assertEquals(0, $user->otp_attempts);
    }

    public function test_verify_returns_true_for_correct_otp(): void
    {
        $user = User::factory()->create();
        $otp = $this->otpService->generate($user);

        $result = $this->otpService->verify($user, $otp);

        $this->assertTrue($result);
    }

    public function test_verify_returns_false_for_incorrect_otp(): void
    {
        $user = User::factory()->create();
        $this->otpService->generate($user);

        $result = $this->otpService->verify($user, '000000');

        $this->assertFalse($result);
    }

    public function test_verify_increments_attempts_on_failure(): void
    {
        $user = User::factory()->create();
        $this->otpService->generate($user);

        $this->otpService->verify($user, '000000');

        $user->refresh();
        $this->assertEquals(1, $user->otp_attempts);
    }

    public function test_verify_does_not_increment_attempts_on_success(): void
    {
        $user = User::factory()->create();
        $otp = $this->otpService->generate($user);

        $this->otpService->verify($user, $otp);

        $user->refresh();
        $this->assertEquals(0, $user->otp_attempts);
    }

    public function test_invalidate_clears_all_otp_fields(): void
    {
        $user = User::factory()->create();
        $this->otpService->generate($user);

        $this->otpService->invalidate($user);

        $user->refresh();
        $this->assertNull($user->otp);
        $this->assertNull($user->otp_expires_at);
        $this->assertEquals(0, $user->otp_attempts);
    }

    public function test_is_expired_returns_true_when_otp_expires_at_is_null(): void
    {
        $user = User::factory()->create(['otp_expires_at' => null]);

        $this->assertTrue($this->otpService->isExpired($user));
    }

    public function test_is_expired_returns_true_when_otp_is_past_expiration(): void
    {
        $user = User::factory()->create([
            'otp_expires_at' => now()->subMinutes(1),
        ]);

        $this->assertTrue($this->otpService->isExpired($user));
    }

    public function test_is_expired_returns_false_when_otp_is_within_window(): void
    {
        $user = User::factory()->create([
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        $this->assertFalse($this->otpService->isExpired($user));
    }
}
