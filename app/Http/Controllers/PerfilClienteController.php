<?php

namespace App\Http\Controllers;

use App\Models\PerfilCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PerfilClienteController extends Controller
{
    public function destroy(PerfilCliente $perfil): RedirectResponse
    {
        abort_unless($perfil->usuario_id === Auth::id(), 404);

        $perfil->delete();

        return back()->with('status', 'Información personal eliminada.');
    }
}
