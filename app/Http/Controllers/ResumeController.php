<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\FileUploadService;
use App\Services\ResumeAnalysisService;
use App\Models\ResumeAnalysis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        try {
            $resumeAnalysisService->analyzeUserResume(
                Auth::user(),
                $resumePath,
                $request->file('resume')->getClientOriginalName()
            );
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($resumePath);
            Log::error('Resume analysis upload failed.', ['user_id' => $userId, 'exception' => $e]);

            return back()->withErrors(['resume' => 'We could not analyze that resume. Please try again.']);
        }

        return redirect()->route('resume.index')->with('success', 'Resume analyzed successfully. Your report is ready.');
    }
}
