<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerInsight;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CareerInsightController extends Controller
{
    /**
     * Display a paginated list of all career insights.
     */
    public function index(): View
    {
        $insights = CareerInsight::query()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->paginate(15);

        return view('admin.insights.index', [
            'insights' => $insights,
        ]);
    }

    /**
     * Show the form for creating a new career insight.
     */
    public function create(): View
    {
        return view('admin.insights.create');
    }

    /**
     * Store a newly created career insight in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:salary,trend,skill'],
            'label' => ['required', 'string', 'max:100'],
            'value' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        CareerInsight::create($validated);

        return redirect()->route('admin.insights.index')
            ->with('success', 'Career insight created successfully.');
    }

    /**
     * Show the form for editing the specified career insight.
     */
    public function edit(CareerInsight $insight): View
    {
        return view('admin.insights.edit', compact('insight'));
    }

    /**
     * Update the specified career insight in the database.
     */
    public function update(Request $request, CareerInsight $insight): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:salary,trend,skill'],
            'label' => ['required', 'string', 'max:100'],
            'value' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $insight->update($validated);

        return redirect()->route('admin.insights.index')
            ->with('success', 'Career insight updated successfully.');
    }

    /**
     * Delete the specified career insight.
     */
    public function destroy(CareerInsight $insight): RedirectResponse
    {
        $insight->delete();

        return redirect()->route('admin.insights.index')
            ->with('success', 'Career insight deleted successfully.');
    }
}
