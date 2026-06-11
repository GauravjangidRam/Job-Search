<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\FileUploadService;
use App\Models\ResumeAnalysis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controller
{
    public function index(): View
    {
        return view('resume.index');
    }

    /**
     * Handle resume analysis upload (authenticated users only)
     */
    public function analyze(Request $request, FileUploadService $fileUploadService): RedirectResponse
    {
        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $userId = Auth::id();
        $resumePath = $fileUploadService->uploadResume($request->file('resume'), $userId);

        try {
            $size = Storage::disk('local')->size($resumePath);
        } catch (\Throwable $e) {
            $size = null;
        }

        try {
            $mime = Storage::disk('local')->mimeType($resumePath);
        } catch (\Throwable $e) {
            $mime = null;
        }

        $analysis = [
            'file_name' => basename($resumePath),
            'size_bytes' => $size,
            'mime_type' => $mime,
            'note' => 'Uploaded by user for quick analysis',
        ];

        ResumeAnalysis::create([
            'job_application_id' => null,
            'resume_path' => $resumePath,
            'analysis' => $analysis,
            'provider' => 'local-metadata',
        ]);

        return redirect()->route('resume.index')->with('success', 'Resume uploaded and analyzed successfully.');
    }
}
