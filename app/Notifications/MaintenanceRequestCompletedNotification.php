<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MaintenanceRequest $maintenanceRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->maintenanceRequest->loadMissing(['property', 'unit']);

        return (new MailMessage)
            ->subject('Your maintenance request has been completed')
            ->line('A technician has marked your maintenance request as completed.')
            ->line("Title: {$this->maintenanceRequest->title}")
            ->line("Property: {$this->maintenanceRequest->property?->name}")
            ->line("Unit: {$this->maintenanceRequest->unit?->name}")
            ->line('Completion notes: '.$this->maintenanceRequest->completion_notes);
    }
}
