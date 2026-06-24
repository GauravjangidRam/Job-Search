<?php

namespace App\Http\Controllers;

use App\Models\JobAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class JobAlertController extends Controller
{
    public function index(): View
    {
        $alerts = Auth::user()->jobAlerts()->orderByDesc('created_at')->get();

        return view('alerts.index', compact('alerts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'keywords' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'job_type' => 'nullable|string|max:50|in:Full-time,Part-time,Contract,Freelance,Internship',
        ]);

        // Require at least one filter
        if (empty($validated['keywords']) && empty($validated['location']) && empty($validated['job_type'])) {
            return back()->with('error', 'Please provide at least one search filter (keyword, location, or job type) for the alert.');
        }

        Auth::user()->jobAlerts()->create($validated);

        return redirect()->route('alerts.index')->with('success', 'Job alert subscription created successfully.');
    }

    public function destroy(JobAlert $alert): RedirectResponse
    {
        abort_if($alert->user_id !== Auth::id(), 403);

        $alert->delete();

        return redirect()->route('alerts.index')->with('success', 'Job alert unsubscribed successfully.');
    }
}
