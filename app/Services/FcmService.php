<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmService
{
    public function send(string $token, string $title, string $body, array $data = []): void
    {
        try {
            $messaging = Firebase::messaging();

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $messaging->send($message);

            Log::info('Notificación FCM (v1) enviada exitosamente a ' . substr($token, 0, 20) . '...');

        } catch (\Exception $e) {
            Log::error('Error al enviar notificación con Firebase Admin SDK: ' . $e->getMessage());
        }
    }
}