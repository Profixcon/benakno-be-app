<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\Message;

class FirebaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $body;
    protected $customData;
    protected $targetApp;

    public function __construct($title, $body, $customData, $targetApp)
    {
        $this->title = $title;
        $this->body = $body;
        $this->customData = $customData;
        $this->targetApp = $targetApp;
    }

    public function via($notifiable)
    {
        return ['firebase'];
    }

    public function toFirebase($notifiable): Message
    {
        return Message::new()
            ->withNotification(Notification::create($this->title, $this->body))
            ->withData(array_merge($this->customData, ['target_app' => $this->targetApp]));
    }
}
