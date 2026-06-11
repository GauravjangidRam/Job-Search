<?php

namespace Tests\Feature;

use Tests\TestCase;

class ResumeControllerTest extends TestCase
{
    public function test_index_returns_200_and_resume_view(): void
    {
        $response = $this->get('/resume');

        $response->assertStatus(200);
        $response->assertViewIs('resume.index');
    }

    public function test_index_contains_resume_tips_section(): void
    {
        $response = $this->get('/resume');

        $response->assertSee('Resume Writing Tips');
        $response->assertSee('Tailor Your Resume to Each Job');
        $response->assertSee('Quantify Your Achievements');
        $response->assertSee('Keep It Concise and Relevant');
    }

    public function test_index_contains_template_suggestions_section(): void
    {
        $response = $this->get('/resume');

        $response->assertSee('Template Suggestions');
        $response->assertSee('Classic Professional');
        $response->assertSee('Modern Minimalist');
        $response->assertSee('Creative Portfolio');
    }

    public function test_index_contains_coming_soon_cta_section(): void
    {
        $response = $this->get('/resume');

        $response->assertSee('Resume Builder Coming Soon');
        $response->assertSee('Coming Soon');
        $response->assertSee('aria-disabled="true"', false);
    }

    public function test_cta_section_contains_expected_content(): void
    {
        $response = $this->get('/resume');

        // The CTA section should not have any anchor tags or form actions that navigate away
        $content = $response->getContent();

        // Extract the coming soon section
        $ctaStart = strpos($content, 'Ready to Apply?');
        $this->assertNotFalse($ctaStart, 'CTA section should exist');   

        // Find the enclosing section
        $sectionStart = strrpos(substr($content, 0, $ctaStart), '<section');
        $sectionEnd = strpos($content, '</section>', $ctaStart);
        $ctaSection = substr($content, $sectionStart, $sectionEnd - $sectionStart + strlen('</section>'));

        // Should not contain any links or form submissions
        $this->assertStringNotContainsString('<a ', $ctaSection);
        $this->assertStringNotContainsString('<form', $ctaSection);
    }
}
