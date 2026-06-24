<?php

namespace App\Console\Commands;

use App\Models\JobAlert;
use App\Models\JobListing;
use App\Mail\JobAlertMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendJobAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily matching job alert notifications to subscribed seekers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting job alert processing...');

        // Fetch new active jobs created in the last 24 hours
        $newJobsQuery = JobListing::active()->where('created_at', '>=', now()->subHours(24));
        $newJobsCount = $newJobsQuery->count();

        if ($newJobsCount === 0) {
            $this->info('No new jobs posted in the last 24 hours. Exiting.');
            return 0;
        }

        $alerts = JobAlert::with('user')->get();
        $sentCount = 0;

        foreach ($alerts as $alert) {
            $query = JobListing::active()->where('created_at', '>=', now()->subHours(24));

            if (!empty($alert->keywords)) {
                $keywords = $alert->keywords;
                $query->where(function ($q) use ($keywords) {
                    $q->where('title', 'LIKE', "%{$keywords}%")
                      ->orWhere('description', 'LIKE', "%{$keywords}%");
                });
            }

            if (!empty($alert->location)) {
                $location = $alert->location;
                $query->where(function ($q) use ($location) {
                    $q->where('location', 'LIKE', "%{$location}%")
                      ->orWhere('location_type', 'LIKE', "%{$location}%");
                });
            }

            if (!empty($alert->job_type)) {
                $query->where('job_type', $alert->job_type);
            }

            $matchingJobs = $query->get();

            if ($matchingJobs->isNotEmpty()) {
                Mail::to($alert->user->email)->queue(new JobAlertMail($alert->user, $matchingJobs));
                $sentCount++;
            }
        }

        $this->info("Completed processing. Sent alerts to {$sentCount} users.");
        return 0;
    }
}
