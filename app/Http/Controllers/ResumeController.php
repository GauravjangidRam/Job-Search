<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

class ResumeController extends Controller
{
    public function index()
    {
        return view('resume');
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'resume' => 'required|file|mimes:pdf,txt|max:5120'
        ]);

        $file = $request->file('resume');
        $ext = strtolower($file->getClientOriginalExtension());
        $text = '';

        if ($ext === 'pdf') {
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile($file->getRealPath());
                $text = $pdf->getText();
            } catch (\Throwable $e) {
                $text = '';
            }
        } elseif ($ext === 'txt') {
            $text = file_get_contents($file->getRealPath());
        }

        $filename = 'resume_' . time() . '.txt';
        Storage::put('resumes/' . $filename, $text);

        // Try sending to a local GPT4All analysis server (if user runs one)
        $aiResponse = null;
        try {
            $resp = Http::timeout(20)->post('http://127.0.0.1:5000/analyze', [
                'text' => $text,
            ]);
            if ($resp->ok()) {
                $aiResponse = $resp->json();
            }
        } catch (\Throwable $e) {
            // ignore — will show instructions instead
        }

        return view('resume', [
            'extracted' => $text,
            'aiResponse' => $aiResponse,
            'savedFile' => $filename,
        ]);
    }
}
