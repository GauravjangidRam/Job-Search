<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\ResumeAnalysis;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ZipArchive;

class ResumeAnalysisService
{
    /**
     * Perform a lightweight analysis of the uploaded resume and persist it.
     * Currently collects basic metadata (filename, size, mime) and stores as JSON.
     */
    public function analyze(JobApplication $application, string $resumePath): ResumeAnalysis
    {
        $analysis = $this->buildReport($resumePath, basename($resumePath));

        return ResumeAnalysis::create([
            'user_id' => $application->user_id,
            'job_application_id' => $application->id,
            'resume_path' => $resumePath,
            'analysis' => $analysis,
            'provider' => 'local-ai-report',
        ]);
    }

    public function analyzeUserResume(User $user, string $resumePath, ?string $originalName = null): ResumeAnalysis
    {
        return ResumeAnalysis::create([
            'user_id' => $user->id,
            'job_application_id' => null,
            'resume_path' => $resumePath,
            'analysis' => $this->buildReport($resumePath, $originalName ?? basename($resumePath)),
            'provider' => 'local-ai-report',
        ]);
    }

    private function buildReport(string $resumePath, string $displayName): array
    {
        $size = $this->readSize($resumePath);
        $mime = $this->readMimeType($resumePath);
        $extension = strtolower(pathinfo($displayName, PATHINFO_EXTENSION));
        $content = $this->extractResumeText($resumePath, $extension);
        $signals = $this->analyzeContentSignals($content);
        $score = $this->calculateScore($size, $extension, $signals);

        $report = [
            'file_name' => $displayName,
            'size_bytes' => $size,
            'mime_type' => $mime,
            'word_count' => $signals['word_count'],
            'detected_sections' => $signals['sections_found'],
            'keyword_count' => count($signals['keywords_found']),
            'score' => $score,
            'summary' => $this->summaryForScore($score, $signals),
            'checks' => $this->buildChecks($size, $mime, $extension, $signals),
            'suggestions' => $this->buildSuggestions($signals, $extension, $size),
        ];

        // If a Gemini API key is configured, augment the report with a model-generated
        // ATS assessment, summary, strengths, and prioritized suggestions.
        try {
            $geminiKey = env('GEMINI_API_KEY');
            if (!empty($geminiKey) && !empty($content)) {
                $external = $this->fetchGeminiAnalysis($content, $geminiKey);

                if (!empty($external['summary'])) {
                    $report['summary'] = $external['summary'];
                }

                if (!empty($external['suggestions']) && is_array($external['suggestions'])) {
                    // merge AI + local suggestions, dedupe, keep top 5
                    $merged = array_values(array_unique(array_merge($external['suggestions'], $report['suggestions'])));
                    $report['suggestions'] = array_slice($merged, 0, 5);
                }

                if (isset($external['ats_score']) && is_numeric($external['ats_score'])) {
                    // Kept alongside the deterministic local `score` rather than overwriting it,
                    // so you can compare the rule-based score against the model's judgement.
                    $report['ai_ats_score'] = max(0, min(100, (int) $external['ats_score']));
                }

                if (!empty($external['strengths'])) {
                    $report['strengths'] = $external['strengths'];
                }

                if (!empty($external['missing_keywords'])) {
                    $report['missing_keywords'] = $external['missing_keywords'];
                }

                $report['external_analysis'] = $external;
            }
        } catch (\Throwable $e) {
            // ignore external failures and keep local report
        }

        return $report;
    }

    private function readSize(string $resumePath): ?int
    {
        try {
            return Storage::disk('local')->size($resumePath);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function readMimeType(string $resumePath): ?string
    {
        try {
            return Storage::disk('local')->mimeType($resumePath);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function calculateScore(?int $size, string $extension, array $signals): int
    {
        $score = 45;

        if (in_array($extension, ['pdf', 'docx'], true)) {
            $score += 10;
        }

        if ($size !== null && $size > 0 && $size <= 2 * 1024 * 1024) {
            $score += 8;
        }

        $score += min(18, count($signals['sections_found']) * 3);
        $score += min(12, count($signals['keywords_found']) * 2);

        if ($signals['has_email']) {
            $score += 5;
        }

        if ($signals['has_phone']) {
            $score += 5;
        }

        if ($signals['has_link']) {
            $score += 4;
        }

        if ($signals['metric_count'] >= 3) {
            $score += 8;
        } elseif ($signals['metric_count'] > 0) {
            $score += 4;
        }

        if ($signals['action_verb_count'] >= 5) {
            $score += 6;
        } elseif ($signals['action_verb_count'] > 0) {
            $score += 3;
        }

        if ($signals['word_count'] >= 300 && $signals['word_count'] <= 900) {
            $score += 8;
        } elseif ($signals['word_count'] > 0) {
            $score += 3;
        }

        if ($extension === 'doc') {
            $score -= 6;
        }

        return max(0, min(100, $score));
    }

    private function summaryForScore(int $score, array $signals): string
    {
        if (! $signals['text_extracted']) {
            return 'The file was uploaded successfully, but text extraction was limited. The report is based on file health and available metadata.';
        }

        if ($score >= 90) {
            return 'Strong resume foundation with clear structure, contact details, measurable impact, and useful job keywords.';
        }

        if ($score >= 80) {
            return 'Good resume foundation. A few targeted improvements can increase ATS match quality.';
        }

        return 'Your resume has useful content, but structure, keywords, contact details, or measurable achievements need improvement.';
    }

    private function buildChecks(?int $size, ?string $mime, string $extension, array $signals): array
    {
        return [
            [
                'label' => 'Supported format',
                'status' => in_array($extension, ['pdf', 'doc', 'docx'], true) ? 'pass' : 'review',
                'detail' => Str::upper($extension ?: 'unknown') . ' file detected.',
            ],
            [
                'label' => 'File size',
                'status' => $size !== null && $size <= 2 * 1024 * 1024 ? 'pass' : 'review',
                'detail' => $size === null ? 'Size could not be read.' : $this->formatBytes($size) . ' uploaded.',
            ],
            [
                'label' => 'ATS readability',
                'status' => in_array($extension, ['pdf', 'docx'], true) ? 'pass' : 'review',
                'detail' => in_array($extension, ['pdf', 'docx'], true)
                    ? 'This format is commonly accepted by modern ATS tools.'
                    : 'Consider uploading a PDF or DOCX version for better ATS compatibility.',
            ],
            [
                'label' => 'Content scan',
                'status' => $signals['text_extracted'] ? 'pass' : 'review',
                'detail' => $signals['text_extracted']
                    ? $signals['word_count'] . ' readable words detected.'
                    : ($mime ? 'Text extraction was limited. MIME type detected as ' . $mime . '.' : 'Text extraction was limited.'),
            ],
            [
                'label' => 'Resume sections',
                'status' => count($signals['sections_found']) >= 4 ? 'pass' : 'review',
                'detail' => count($signals['sections_found']) > 0
                    ? implode(', ', $signals['sections_found']) . ' detected.'
                    : 'Add clear section headings like Summary, Experience, Skills, Education, and Projects.',
            ],
            [
                'label' => 'Contact details',
                'status' => $signals['has_email'] && $signals['has_phone'] ? 'pass' : 'review',
                'detail' => $signals['has_email'] && $signals['has_phone']
                    ? 'Email and phone number detected.'
                    : 'Add both email and phone number near the top of the resume.',
            ],
            [
                'label' => 'Measurable impact',
                'status' => $signals['metric_count'] >= 3 ? 'pass' : 'review',
                'detail' => $signals['metric_count'] . ' metric-based achievements detected.',
            ],
            [
                'label' => 'Job keywords',
                'status' => count($signals['keywords_found']) >= 5 ? 'pass' : 'review',
                'detail' => count($signals['keywords_found']) > 0
                    ? implode(', ', array_slice($signals['keywords_found'], 0, 8)) . ' detected.'
                    : 'Add relevant role, tool, and skill keywords from the job description.',
            ],
        ];
    }

    private function extractResumeText(string $resumePath, string $extension): string
    {
        try {
            $absolutePath = Storage::disk('local')->path($resumePath);
        } catch (\Throwable $e) {
            return '';
        }

        if ($extension === 'docx') {
            return $this->extractDocxText($absolutePath);
        }

        if ($extension === 'pdf') {
            return $this->extractPdfText($absolutePath);
        }

        return '';
    }

    private function extractDocxText(string $absolutePath): string
    {
        if (! class_exists(ZipArchive::class)) {
            return '';
        }

        $zip = new ZipArchive();

        if ($zip->open($absolutePath) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        if ($xml === '') {
            return '';
        }

        $xml = str_replace(['</w:p>', '</w:tab>'], ["\n", ' '], $xml);

        return $this->normalizeText(strip_tags($xml));
    }

    private function extractPdfText(string $absolutePath): string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf    = $parser->parseFile($absolutePath);
            $text = $pdf->getText();
            return $this->normalizeText($text);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function analyzeContentSignals(string $content): array
    {
        $normalized = $this->normalizeText($content);
        $lower = Str::lower($normalized);
        $words = preg_split('/\s+/', trim($normalized), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $sectionPatterns = [
            'Summary' => '/\b(summary|profile|objective)\b/i',
            'Experience' => '/\b(experience|employment|work history|professional experience)\b/i',
            'Skills' => '/\b(skills|technical skills|technologies|tools)\b/i',
            'Education' => '/\b(education|degree|university|college)\b/i',
            'Projects' => '/\b(projects|portfolio)\b/i',
            'Certifications' => '/\b(certifications|certificates|licenses)\b/i',
        ];

        $sectionsFound = [];
        foreach ($sectionPatterns as $section => $pattern) {
            if (preg_match($pattern, $normalized)) {
                $sectionsFound[] = $section;
            }
        }

        $keywords = [
            'php', 'laravel', 'javascript', 'typescript', 'react', 'vue', 'node', 'python',
            'sql', 'mysql', 'postgresql', 'api', 'aws', 'docker', 'git', 'testing',
            'leadership', 'communication', 'analytics', 'management', 'sales', 'marketing',
            'design', 'figma', 'excel', 'power bi', 'machine learning', 'data',
        ];

        $keywordsFound = [];
        foreach ($keywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                $keywordsFound[] = Str::title($keyword);
            }
        }

        preg_match_all('/\b(?:increased|reduced|improved|led|built|created|launched|managed|optimized|designed|developed|implemented|delivered|automated|achieved)\b/i', $normalized, $actionVerbMatches);
        preg_match_all('/(?:\b\d+(?:\.\d+)?\s?%|\b\d+(?:\.\d+)?\s?(?:k|m|million|hours|days|users|clients|projects|revenue|sales)\b)/i', $normalized, $metricMatches);

        return [
            'text_extracted' => str_word_count($normalized) > 20,
            'word_count' => count($words),
            'sections_found' => $sectionsFound,
            'keywords_found' => array_values(array_unique($keywordsFound)),
            'has_email' => (bool) preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $normalized),
            'has_phone' => (bool) preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $normalized),
            'has_link' => (bool) preg_match('/\b(?:https?:\/\/|linkedin\.com|github\.com|portfolio)\b/i', $normalized),
            'metric_count' => count($metricMatches[0] ?? []),
            'action_verb_count' => count($actionVerbMatches[0] ?? []),
        ];
    }

    private function buildSuggestions(array $signals, string $extension, ?int $size): array
    {
        $suggestions = [];

        if (! $signals['text_extracted']) {
            $suggestions[] = 'Use a text-based PDF or DOCX file so the analyzer and ATS tools can read your resume content.';
        }

        if (count($signals['sections_found']) < 4) {
            $suggestions[] = 'Add clear headings for Summary, Experience, Skills, Education, and Projects so recruiters can scan quickly.';
        }

        if (! $signals['has_email'] || ! $signals['has_phone']) {
            $suggestions[] = 'Place your email and phone number near the top so employers can contact you easily.';
        }

        if (! $signals['has_link']) {
            $suggestions[] = 'Add a LinkedIn, GitHub, or portfolio link when it supports the roles you are applying for.';
        }

        if ($signals['metric_count'] < 3) {
            $suggestions[] = 'Add measurable results to more bullet points, such as percentages, revenue, users, time saved, or project counts.';
        }

        if ($signals['action_verb_count'] < 5) {
            $suggestions[] = 'Start more bullets with strong action verbs like Led, Built, Improved, Optimized, Delivered, or Automated.';
        }

        if (count($signals['keywords_found']) < 5) {
            $suggestions[] = 'Add more role-specific skills and tools from the job description to improve keyword matching.';
        }

        if ($signals['word_count'] > 900) {
            $suggestions[] = 'Shorten the resume by removing older or less relevant details; aim for the strongest recent experience.';
        } elseif ($signals['word_count'] > 0 && $signals['word_count'] < 300) {
            $suggestions[] = 'Add more detail about responsibilities, tools, projects, and measurable results.';
        }

        if ($extension === 'doc') {
            $suggestions[] = 'Convert the resume to PDF or DOCX for better ATS compatibility.';
        }

        if ($size !== null && $size > 2 * 1024 * 1024) {
            $suggestions[] = 'Reduce file size by compressing images or exporting a clean text-based PDF.';
        }

        return array_slice(array_unique($suggestions), 0, 5);
    }

    /**
     * Ask Gemini to act as an experienced resume/ATS reviewer and return a
     * compact, strictly-structured JSON verdict. Using responseSchema (instead
     * of free text) means no fragile regex parsing on our side, and a tight
     * maxOutputTokens keeps each call cheap and fast.
     */
    private function fetchGeminiAnalysis(string $content, string $apiKey): array
    {
        // gemini-2.0-flash / flash-lite were retired June 2026; gemini-2.5-flash is
        // the current low-cost, low-latency default. Override via GEMINI_MODEL in .env.
        $model = env('GEMINI_MODEL', 'gemini-2.5-flash');
        $baseUrl = env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta');
        $url = rtrim($baseUrl, '/') . '/models/' . $model . ':generateContent';

        // Persona + rules. Kept short on purpose: every extra sentence here is
        // extra input tokens on every single resume analyzed.
        $persona = 'You are a senior technical recruiter and ATS (Applicant Tracking System) '
            . 'specialist with 12+ years of experience screening resumes for tech and corporate roles. '
            . 'Judge the resume strictly on structure, keyword relevance, measurable achievements, and '
            . 'contact completeness. Be concise: short, direct sentences, no filler, no markdown.';

        // Cap input length so a long resume (or someone pasting a whole portfolio) never
        // balloons the request — a 1-2 page resume comfortably fits in 6000 characters.
        $promptText = $persona . "\n\nResume text:\n" . Str::limit($content, 6000, '');

        $responseSchema = [
            'type' => 'object',
            'properties' => [
                'ats_score' => [
                    'type' => 'integer',
                    'description' => 'Overall ATS-compatibility and content-quality score, 0-100.',
                ],
                'summary' => [
                    'type' => 'string',
                    'description' => 'One to two sentence overall verdict.',
                ],
                'strengths' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'maxItems' => 3,
                ],
                'suggestions' => [
                    'type' => 'array',
                    'description' => 'Prioritized, actionable improvements, most important first.',
                    'items' => ['type' => 'string'],
                    'maxItems' => 5,
                ],
                'missing_keywords' => [
                    'type' => 'array',
                    'description' => 'Relevant role/tool/skill keywords the resume is missing.',
                    'items' => ['type' => 'string'],
                    'maxItems' => 8,
                ],
            ],
            'required' => ['ats_score', 'summary', 'suggestions'],
        ];

        $body = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $promptText]]],
            ],
            'generationConfig' => [
                'maxOutputTokens' => 500,
                'temperature' => 0.4,
                'responseMimeType' => 'application/json',
                'responseSchema' => $responseSchema,
            ],
        ];

        try {
            $resp = Http::withOptions(['verify' => true])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->timeout(20)
                ->post($url . '?key=' . $apiKey, $body);

            if (! $resp->successful()) {
                return ['error' => 'Gemini request failed', 'status' => $resp->status(), 'raw' => $resp->body()];
            }
            $json = $resp->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (! $text) {
                return ['error' => 'Empty response from Gemini', 'response_json' => $json];
            }

            $parsed = json_decode($text, true);

            if (! is_array($parsed)) {
                return ['error' => 'Could not parse Gemini JSON response', 'raw' => $text];
            }

            return [
                'ats_score' => $parsed['ats_score'] ?? null,
                'summary' => $parsed['summary'] ?? null,
                'strengths' => array_values(array_filter($parsed['strengths'] ?? [])),
                'suggestions' => array_slice(array_values(array_filter($parsed['suggestions'] ?? [])), 0, 5),
                'missing_keywords' => array_slice(array_values(array_filter($parsed['missing_keywords'] ?? [])), 0, 8),
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function normalizeText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $text = preg_replace('/[^\P{C}\n\t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1) . ' MB';
        }

        return max(1, round($bytes / 1024)) . ' KB';
    }
}