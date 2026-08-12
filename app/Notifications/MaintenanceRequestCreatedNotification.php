<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MaintenanceRequest $maintenanceRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->maintenanceRequest->loadMissing(['property', 'unit', 'resident']);

        return (new MailMessage)
            ->subject('New maintenance request reported')
            ->line('A resident has reported a new maintenance request.')
            ->line("Title: {$this->maintenanceRequest->title}")
            ->line("Resident: {$this->maintenanceRequest->resident?->name}")
            ->line("Property: {$this->maintenanceRequest->property?->name}")
            ->line("Unit: {$this->maintenanceRequest->unit?->name}")
            ->line("Category: {$this->maintenanceRequest->category->getLabel()}")
            ->line("Priority: {$this->maintenanceRequest->priority->getLabel()}")
            ->line($this->maintenanceRequest->description);
    }
}
