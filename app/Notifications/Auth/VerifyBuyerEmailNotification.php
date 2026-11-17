<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyBuyerEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verifyUrl = URL::temporarySignedRoute(
            'auth.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject('Confirma tu correo electrónico — MARTE Store')
            ->greeting('Hola ' . ($notifiable->name ?: 'comprador') . ',')
            ->line('Gracias por crear tu cuenta. Confirma tu correo para vincular tus pedidos anteriores y habilitar la protección en dos pasos (2FA).')
            ->action('Confirmar mi correo', $verifyUrl)
            ->line('Este enlace expira en 60 minutos. Si no creaste esta cuenta, puedes ignorar este mensaje.');
    }
}
