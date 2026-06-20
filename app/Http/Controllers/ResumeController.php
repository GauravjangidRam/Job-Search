<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\FileUploadService;
use App\Services\ResumeAnalysisService;
use App\Models\ResumeAnalysis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ResumeController extends Controller
{
    public function index(): View
    {
        $latestAnalysis = ResumeAnalysis::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        return view('resume.index', [
            'latestAnalysis' => $latestAnalysis,
        ]);
    }

    /**
     * Handle resume analysis upload (authenticated users only)
     */
    public function analyze(
        Request $request,
        FileUploadService $fileUploadService,
        ResumeAnalysisService $resumeAnalysisService
    ): RedirectResponse
    {
        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $userId = Auth::id();
        $resumePath = $fileUploadService->uploadResume($request->file('resume'), $userId);

        $resumeAnalysisService->analyzeUserResume(
            Auth::user(),
            $resumePath,
            $request->file('resume')->getClientOriginalName()
        );

        return redirect()->route('resume.index')->with('success', 'Resume analyzed successfully. Your report is ready.');
    }
}