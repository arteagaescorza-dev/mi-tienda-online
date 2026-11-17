<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetBuyerPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url('/?tab=account&reset_token=' . urlencode($this->token) . '&reset_email=' . urlencode($notifiable->email));

        return (new MailMessage)
            ->subject('Recuperar contraseña — MARTE Store')
            ->greeting('Hola ' . ($notifiable->name ?: 'comprador') . ',')
            ->line('Recibimos una solicitud para restablecer tu contraseña.')
            ->action('Restablecer contraseña', $resetUrl)
            ->line('Este enlace expira en 60 minutos. Si tienes 2FA activo, se te pedirá tu código de verificación al guardar la nueva contraseña.');
    }
}
