<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MaintenanceRequest $maintenanceRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->maintenanceRequest->loadMissing(['property', 'unit']);

        return (new MailMessage)
            ->subject('You have been assigned a maintenance request')
            ->line("You've been assigned to the following maintenance request:")
            ->line("Title: {$this->maintenanceRequest->title}")
            ->line("Property: {$this->maintenanceRequest->property?->name}")
            ->when(
                $this->maintenanceRequest->unit !== null,
                fn (MailMessage $message): MailMessage => $message->line(
                    "Unit: {$this->maintenanceRequest->unit?->name}",
                ),
            )
            ->line("Priority: {$this->maintenanceRequest->priority->value}")
            ->line($this->maintenanceRequest->description);
    }
}
