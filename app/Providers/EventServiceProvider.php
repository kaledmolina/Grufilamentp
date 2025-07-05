<?php
namespace App\Providers;

use App\Models\Orden; // <-- Añadir
use App\Observers\OrdenObserver; // <-- Añadir
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $observers = [
        Orden::class => [OrdenObserver::class], // <-- Añadir esta línea
    ];
    // ...
}
