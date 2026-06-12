<?php

namespace Tests\Feature;

use App\Models\ResumeAnalysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumeControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_guest_is_redirected_to_login_before_resume_page(): void
    {
        $response = $this->get('/resume');
        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_before_resume_analysis_upload(): void
    {
        $response = $this->post('/resume/analyze', [
            'resume' => UploadedFile::fake()->create('resume.pdf', 128, 'application/pdf'),
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_resume_analysis_page(): void
    {
        $user = User::factory()->create(['role' => 'seeker']);

        $response = $this->actingAs($user)->get('/resume');

        $response->assertStatus(200);
        $response->assertViewIs('resume.index');
        $response->assertSee('AI Resume Analysis');
        $response->assertSee('Upload Resume');
        $response->assertSee('Analysis mode: Local AI report');
        $response->assertSee('Your report will appear here');
        $response->assertSee('id="resume-processing"', false);
    }

    public function test_authenticated_user_can_upload_resume_and_get_report(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'seeker']);
        $resume = UploadedFile::fake()->create('resume.pdf', 256, 'application/pdf');

        $response = $this->actingAs($user)->post('/resume/analyze', [
            'resume' => $resume,
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('resume.index'));
        $response->assertSessionHas('success', 'Resume analyzed successfully. Your report is ready.');

        $analysis = ResumeAnalysis::query()->firstOrFail();

        $this->assertSame($user->id, $analysis->user_id);
        $this->assertSame('local-ai-report', $analysis->provider);
        $this->assertNull($analysis->job_application_id);
        $this->assertSame('resume.pdf', $analysis->analysis['file_name']);
        $this->assertArrayHasKey('score', $analysis->analysis);
        $this->assertArrayHasKey('checks', $analysis->analysis);
        $this->assertArrayHasKey('suggestions', $analysis->analysis);
        Storage::disk('local')->assertExists($analysis->resume_path);
        $page = $this->actingAs($user)->get('/resume');

        $page->assertSee('Latest report');
        $page->assertSee('resume.pdf');
        $page->assertSee('Readiness score');
        $page->assertSee('Suggested improvements');
    }

    public function test_resume_upload_requires_supported_document_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'seeker']);

        $response = $this->actingAs($user)->post('/resume/analyze', [
            'resume' => UploadedFile::fake()->image('avatar.png'),
        ]);
        $response->assertSessionHasErrors('resume');
        $this->assertDatabaseCount('resume_analyses', 0);
    }
}