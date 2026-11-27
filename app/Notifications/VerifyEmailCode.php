<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailCode extends Notification
{
    public function __construct(
        public string $code,
        public string $verificationUrl,
    ) {}

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
            ->subject('Your deliBEE verification code')
            ->greeting('Verify your deliBEE email')
            ->line('Your six-digit verification code is:')
            ->line("# {$this->code}")
            ->line('The code expires in 10 minutes.')
            ->action('Verify with a link instead', $this->verificationUrl)
            ->line('If you did not create a deliBEE account, you can ignore this email.');
    }
}
