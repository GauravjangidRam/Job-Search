<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ResumeController extends Controller
{
    public function index(): View
    {
        return view('resume.index');
    }
}
