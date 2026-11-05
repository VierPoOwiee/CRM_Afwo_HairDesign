<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
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
            ->subject('Password Anda Baru Saja Diubah — AFWO Hair Design')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Password akun AFWO Hair Design Anda baru saja diubah.')
            ->line('Jika Anda tidak melakukan perubahan ini, segera hubungi owner atau gunakan fitur lupa password.')
            ->salutation('Salam, AFWO Hair Design');
    }
}
