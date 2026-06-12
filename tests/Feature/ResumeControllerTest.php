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

        // Blade ke according update
        $response->assertSee('Tailor to Each Job');
        $response->assertSee('Quantify Achievements');
        $response->assertSee('Keep It Concise');
    }

    public function test_index_contains_template_suggestions_section(): void
    {
        $response = $this->get('/resume');

        // Blade heading update
        $response->assertSee('Choose the Right Template');

        $response->assertSee('Classic Professional');
        $response->assertSee('Modern Minimalist');
        $response->assertSee('Creative Portfolio');
        $response->assertSee('Technical Specialist');
    }

    public function test_index_contains_cta_section(): void
    {
        $response = $this->get('/resume');

        $response->assertSee('Ready to Apply?');
        $response->assertSee('Browse Jobs');
    }

    public function test_cta_section_contains_expected_content(): void
    {
        $response = $this->get('/resume');

        $content = $response->getContent();

        $ctaStart = strpos($content, 'Ready to Apply?');

        $this->assertNotFalse(
            $ctaStart,
            'CTA section should exist'
        );

        $sectionStart = strrpos(
            substr($content, 0, $ctaStart),
            '<section'
        );

        $sectionEnd = strpos(
            $content,
            '</section>',
            $ctaStart
        );

        $ctaSection = substr(
            $content,
            $sectionStart,
            $sectionEnd - $sectionStart + strlen('</section>')
        );

        // Ab CTA me link expected hai
        $this->assertStringContainsString('<a ', $ctaSection);

        $this->assertStringContainsString(
            'Browse Jobs',
            $ctaSection
        );

        // Form nahi hona chahiye
        $this->assertStringNotContainsString(
            '<form',
            $ctaSection
        );
    }
}