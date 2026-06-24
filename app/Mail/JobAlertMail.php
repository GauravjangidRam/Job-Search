<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class JobAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Collection $jobs;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Collection $jobs)
    {
        $this->user = $user;
        $this->jobs = $jobs;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Daily Job Alerts - Matching Jobs Found!')
                    ->markdown('emails.job_alert');
    }
}
