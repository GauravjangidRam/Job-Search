<?php

namespace App\Http\Controllers;

use App\Models\CareerInsight;
use Illuminate\View\View;

class CareerInsightController extends Controller
{
    public function index(): View
    {
        $types = ['salary', 'trend', 'skill'];

        $grouped = collect();

        foreach ($types as $type) {
            $grouped[$type] = CareerInsight::where('type', $type)
                ->orderBy('sort_order', 'asc')
                ->limit(20)
                ->get();
        }

        return view('insights.index', [
            'insights' => $grouped,
        ]);
    }
}
