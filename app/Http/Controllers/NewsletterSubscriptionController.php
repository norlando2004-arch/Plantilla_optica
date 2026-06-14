<?php

namespace App\Http\Controllers;

use App\Models\SuscripcionBoletin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
        ], [
            'email.required' => 'Ingresa un email.',
            'email.email' => 'Ingresa un email válido.',
            'email.max' => 'El email es demasiado largo.',
        ]);

        if ($validator->fails()) {
            return redirect()->to(url('/') . '#newsletter')
                ->withErrors($validator)
                ->withInput();
        }

        $email = mb_strtolower(trim((string) $request->input('email')));

        SuscripcionBoletin::query()->updateOrCreate(
            ['correo' => $email],
            [
                'usuario_id' => auth()->check() ? (int) auth()->id() : null,
                'estado' => 'suscrito',
                'origen' => 'landing_newsletter',
                'suscrito_en' => now(),
                'cancelado_en' => null,
                'meta' => null,
            ]
        );

        return redirect()->to(url('/') . '#newsletter')
            ->with('newsletter_status', '¡Gracias! Te registramos para recibir novedades.');
    }
}
