@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Resume Analyzer (GPT4All)</h1>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('resume.analyze') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="resume" class="form-label">Upload resume (PDF or TXT, max 5MB)</label>
            <input class="form-control" type="file" id="resume" name="resume" required>
        </div>
        <button class="btn btn-primary" type="submit">Analyze</button>
    </form>

    @isset($extracted)
        <hr>
        <h2>Extracted Text</h2>
        <pre style="white-space: pre-wrap;">{{ $extracted ?: 'No text extracted' }}</pre>

        @if($aiResponse)
            <hr>
            <h2>AI Suggestions</h2>
            <div>
                @if(is_array($aiResponse))
                    <pre style="white-space: pre-wrap;">{{ json_encode($aiResponse, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <pre style="white-space: pre-wrap;">{{ $aiResponse }}</pre>
                @endif
            </div>
        @else
            <hr>
            <h2>How to run GPT4All locally (no payment)</h2>
            <ol>
                <li>Download GPT4All (https://gpt4all.io/) or a CPU-quantized ggml model (gpt4all-3b or similar).</li>
                <li>Run a local server or CLI to accept POST /analyze and return JSON suggestions. Example simple server is provided in the project README (instructions).</li>
                <li>Re-run upload to send extracted text to the local server and receive recommendations.</li>
            </ol>
            <p>No payment or account required for GPT4All models; they run locally.</p>
        @endif
    @endisset
</div>
@endsection
