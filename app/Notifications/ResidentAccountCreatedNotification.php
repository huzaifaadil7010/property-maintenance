<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResidentAccountCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $temporaryPassword) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your resident account is ready')
            ->line('Your resident account has been created.')
            ->line("Email: {$notifiable->email}")
            ->line("Temporary password: {$this->temporaryPassword}")
            ->action('Sign in', route('login'))
            ->line('Use the temporary password to sign in, then update it from your profile.');
    }
}
