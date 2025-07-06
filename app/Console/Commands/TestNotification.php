<?php
namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\OrderAssignedNotification;
use App\Models\Orden;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // El comando se llamará: app:test-notification
    // Acepta un argumento {userId} que es el ID del usuario al que se le enviará la notificación.
    protected $signature = 'app:test-notification {userId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía una notificación de prueba a un usuario específico.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('userId');
        $this->info("Buscando al usuario con ID: {$userId}...");

        $user = User::find($userId);

        if (!$user) {
            $this->error("Error: Usuario con ID {$userId} no encontrado.");
            return 1; // Termina el comando con un código de error
        }

        $this->info("Usuario '{$user->name}' encontrado.");

        if (!$user->fcm_token) {
            $this->error("Error: El usuario '{$user->name}' no tiene un token FCM registrado en la base de datos.");
            return 1;
        }

        $this->info("Token FCM encontrado: " . substr($user->fcm_token, 0, 30) . "...");

        try {
            // Creamos una "orden falsa" solo para poder pasarla a la notificación.
            // Esto asegura que la clase de notificación funcione como se espera.
            $fakeOrder = new Orden([
                'id' => 999,
                'address' => 'Dirección de prueba',
                'status' => 'abierta'
            ]);

            $this->info("Enviando notificación de prueba...");
            
            // Enviamos la notificación
            $user->notify(new OrderAssignedNotification($fakeOrder));

            $this->info("✅ ¡Notificación enviada a la cola exitosamente!");
            $this->comment("Revisa tu dispositivo o el log de Laravel (`storage/logs/laravel.log`) para confirmar el envío a Firebase.");

        } catch (\Exception $e) {
            $this->error("❌ Ocurrió un error al enviar la notificación:");
            $this->error($e->getMessage());
            return 1;
        }

        return 0; // Termina el comando con éxito
    }
}
