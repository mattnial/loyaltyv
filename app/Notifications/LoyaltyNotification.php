<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LoyaltyNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $type;

    public function __construct($title, $message, $type = 'info')
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
    }

    // 1. IMPORTANTE: Definir el canal 'database'
    public function via($notifiable)
    {
        return ['database']; 
    }

    // 2. Guardar los datos en la tabla 'notifications'
    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'date' => now()->toDateTimeString()
        ];
    }
}