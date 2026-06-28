<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationReceivedNotification extends Notification
{
    use Queueable;

    public JobApplication $application;

    /**
     * Create a new notification instance.
     */
    public function __construct(JobApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    } 

    /** 
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_received',
            'application_id' => $this->application->id,
            'job_title' => $this->application->jobListing->title,
            'applicant_name' => $this->application->applicant_name,
            'message' => 'New application received for ' . $this->application->jobListing->title . ' from ' . $this->application->applicant_name,
        ];
    }
}