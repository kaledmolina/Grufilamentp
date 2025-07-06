<?php
// Abre el archivo app/Console/Commands/TestNotification.php
// y reemplaza todo su contenido con este código.

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FcmService; // <-- Importante: Usa el FcmService
use Illuminate\Console\Command;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-notification {userId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía una notificación de prueba usando el FcmService (método moderno).';

    /**
     * Execute the console command.
     */
    // CAMBIO CLAVE: Inyectamos el FcmService aquí
    public function handle(FcmService $fcmService)
    {
        $userId = $this->argument('userId');
        $user = User::find($userId);

        if (!$user || !$user->fcm_token) {
            $this->error('Usuario no encontrado o no tiene token FCM.');
            return 1;
        }

        $this->info("Usuario '{$user->name}' encontrado.");
        $this->info("Token FCM encontrado: " . substr($user->fcm_token, 0, 30) . "...");
        $this->info("Enviando notificación de prueba a través del FcmService...");

        // CAMBIO CLAVE: Ya no usamos ->notify(), sino que llamamos directamente a nuestro servicio.
        $fcmService->send(
            $user->fcm_token,
            'Notificación de Prueba (Moderna)',
            'Esta es una prueba desde el comando de Artisan.'
        );

        $this->info('✅ Comando ejecutado. Revisa tu dispositivo y el log en `storage/logs/laravel.log` para ver el resultado del envío.');
        return 0;
    }
}