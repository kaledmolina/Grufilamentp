<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Livewire\Features\SupportRedirects\Redirector
     */
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();

        // Evitar redirecciones no deseadas si Livewire quedó guardado en url.intended
        $intended = session()->get('url.intended');
        if ($intended && str_contains($intended, 'livewire')) {
            session()->forget('url.intended');
        }

        // Si es cliente o técnico, redirige directamente a la lista de órdenes
        if ($user && $user->hasRole(['cliente', 'tecnico'])) {
            return redirect()->to(route('filament.admin.resources.ordens.index'));
        }

        // Para cualquier otro rol (admin, operador), redirige a la URL intentada válida o al dashboard
        $dashboardUrl = route('filament.admin.pages.dashboard');

        return redirect()->intended($dashboardUrl);
    }
}
