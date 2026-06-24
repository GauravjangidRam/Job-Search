<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $jobs = JobListing::active()->latest()->get();
        $companies = Company::all();

        $content = view('sitemap.index', [
            'jobs' => $jobs,
            'companies' => $companies
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
