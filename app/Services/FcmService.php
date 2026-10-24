<?php

namespace App\Services;

use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\MessagingException;
use Throwable;

class FcmService
{
    public function __construct() {}

    public function registerToken(User $user, string $token, ?string $deviceName = null): FcmToken
    {
        return FcmToken::updateOrCreate(
            [
                'user_id' => $user->getKey(),
                'token' => $token,
            ],
            [
                'device_name' => $deviceName,
            ]
        );
    }

    /**
     * @throws FirebaseException
     */
    public function sendToUser(User $user, array $notification, array $data = []): bool
    {
        $tokens = $user->fcmTokens()->pluck('token')->filter()->values();

        if ($tokens->isEmpty()) {
            return false;
        }

        $messaging = app(Messaging::class);
        $sentCount = 0;

        foreach ($tokens as $token) {
            $payload = [
                'token' => $token,
                'notification' => $notification,
            ];

            if (! empty($data)) {
                $normalizedData = [];

                foreach ($data as $key => $value) {
                    $normalizedData[(string) $key] = (string) $value;
                }

                $payload['data'] = $normalizedData;
            }

            try {
                $messaging->send($payload);
                $sentCount++;
            } catch (MessagingException $exception) {
                if ($this->shouldDeleteToken($exception)) {
                    FcmToken::where('token', $token)->delete();
                    Log::info("Token FCM inactivo/desregistrado eliminado automáticamente: {$token}");
                    continue;
                }

                Log::warning("Error al enviar notificación FCM: " . $exception->getMessage(), [
                    'token' => $token,
                    'user_id' => $user->getKey(),
                ]);
            } catch (Throwable $exception) {
                Log::error("Error inesperado en servicio FCM: " . $exception->getMessage(), [
                    'token' => $token,
                    'user_id' => $user->getKey(),
                ]);
            }
        }

        return $sentCount > 0;
    }

    private function shouldDeleteToken(Throwable $exception): bool
    {
        if ($exception instanceof NotFound) {
            return true;
        }

        $message = Str::lower($exception->getMessage());

        return Str::contains($message, [
            'device unregistered',
            'unregistered',
            'notregistered',
            'not registered',
            'requested entity was not found',
            'not found',
            'invalid registration token',
            'registration-token-not-registered',
            'mismatch',
        ]);
    }
}
