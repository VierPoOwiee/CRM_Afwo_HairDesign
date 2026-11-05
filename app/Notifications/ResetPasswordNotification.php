<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Atur Ulang Password — AFWO Hair Design')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kami menerima permintaan untuk mengatur ulang password akun AFWO Hair Design Anda.')
            ->action('Atur Ulang Password', $url)
            ->line('Tautan ini berlaku selama 30 menit.')
            ->line('Abaikan email ini jika bukan Anda yang meminta pengaturan ulang password.')
            ->salutation('Salam, AFWO Hair Design');
    }
}
