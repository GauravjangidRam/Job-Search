<?php

namespace App\Services;

use App\Mail\ApplicationReceivedMail;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApplicationNotificationService
{
    /**
     * Notify the employer that a new application has been received.
     *
     * Looks up the employer via the application's job listing → company → employers
     * relationship and emails them the ApplicationReceivedMail. If there is no
     * associated job listing, company, or employer, the method returns silently.
     *
     * Mail sending is wrapped in a try/catch that logs failures without throwing,
     * so a mail failure never blocks application submission.
     */
    public function notifyEmployer(JobApplication $application): void
    {
        $jobListing = $application->jobListing;

        if ($jobListing === null) {
            return;
        }
        $company = $jobListing->company;

        if ($company === null) {
            return;
        }

        $employer = $company->employers()->first();

        if ($employer === null || empty($employer->email)) {
            return;
        }

        try {
            Mail::to($employer->email)->send(new ApplicationReceivedMail($application));
        } catch (\Throwable $e) {
            Log::error('Failed to send application received notification to employer.', [
                'application_id' => $application->id,
                'employer_email' => $employer->email,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}