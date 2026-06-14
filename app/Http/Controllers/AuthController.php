<?php

namespace App\Http\Controllers;

use App\Mail\VerifyRegistrationCodeMail;
use App\Services\GuestShopperService;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        try {
            $credentials = $request->validate(
                [
                    'correo' => ['required', 'email:rfc,dns', 'max:255'],
                    'contrasena' => ['required', 'string', 'min:8', 'max:72'],
                ],
                $this->authValidationMessages(),
            );

            $remember = $request->boolean('recordarme');

            $attemptData = [
                'correo' => mb_strtolower(trim((string) $credentials['correo'])),
                'password' => $credentials['contrasena'],
                'esta_activo' => true,
            ];

            if (Auth::attempt($attemptData, $remember)) {
                $request->session()->regenerate();
                GuestShopperService::mergeIntoUser($request, (int) Auth::id());

                return redirect()->intended(route('landing'));
            }

            return $this->failedAuthResponse($request, 'Las credenciales proporcionadas no coinciden con nuestros registros.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('Error controlado en login de paciente', [
                'correo' => (string) $request->input('correo', ''),
                'error' => $e->getMessage(),
            ]);

            return $this->failedAuthResponse($request, 'No pudimos iniciar sesión en este momento. Intenta nuevamente en unos segundos.');
        }
    }

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        try {
            $data = $request->validate(
                [
                    'nombre' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s\'\-\.]+$/u'],
                    'correo' => ['required', 'email:rfc,dns', 'max:255', 'unique:usuarios,correo'],
                    'contrasena' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
                ],
                $this->registerValidationMessages(),
            );

            $normalizedName = preg_replace('/\s+/u', ' ', trim((string) $data['nombre'])) ?? trim((string) $data['nombre']);
            $normalizedEmail = mb_strtolower(trim((string) $data['correo']));
            // Generar código de verificación de 6 dígitos
            $code = (string) random_int(100000, 999999);

            // Guardar datos del registro pendiente en sesión (sin tocar la base de datos aún)
            $request->session()->put('pending_registration', [
                'nombre' => $normalizedName,
                'correo' => $normalizedEmail,
                'contrasena' => $data['contrasena'],
            ]);

            $request->session()->put('pending_registration_code', $code);
            $request->session()->put('pending_registration_expires_at', now()->addMinutes(15)->toIso8601String());

            // Enviar correo con el código de verificación al correo ingresado por el paciente
            try {
                $brevoEnabledEnv = env('USE_BREVO_API', true);
                $brevoApiKey = config('services.brevo.api_key') ?? env('BREVO_API_KEY');
                $fromAddress = config('mail.from.address');

                $canUseBrevo = $brevoEnabledEnv
                    && !empty($brevoApiKey)
                    && !empty($fromAddress);

                if ($canUseBrevo) {
                    $html = view('emails.auth.verify_registration_code', [
                        'code' => $code,
                        'nombre' => $normalizedName,
                    ])->render();

                    $sent = app(\App\Services\BrevoMailer::class)->send(
                        $normalizedEmail,
                        $normalizedName,
                        'Tu código de verificación - Óptica',
                        $html,
                    );

                    if (!$sent) {
                        Log::warning('Registro: Brevo API falló al enviar código de verificación, usando fallback Mail::send', [
                            'correo' => $normalizedEmail,
                        ]);

                        Mail::to($normalizedEmail)->send(new VerifyRegistrationCodeMail($code, $normalizedName));
                    }
                } else {
                    if (app()->environment('production')) {
                        Log::info('Registro: Brevo deshabilitado o mal configurado, usando Mail::send para código de verificación', [
                            'correo' => $normalizedEmail,
                            'USE_BREVO_API' => $brevoEnabledEnv,
                            'has_api_key' => !empty($brevoApiKey),
                            'has_from' => !empty($fromAddress),
                        ]);
                    }

                    Mail::to($normalizedEmail)->send(new VerifyRegistrationCodeMail($code, $normalizedName));
                }
            } catch (Throwable $e) {
                Log::error('Error enviando correo de verificación de registro', [
                    'correo' => $normalizedEmail,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'pending_verification',
                    'message' => 'Te enviamos un código de verificación de 6 dígitos a ' . $normalizedEmail . '.',
                    'redirect' => route('register.verify.show'),
                ], 202);
            }

            return redirect()
                ->route('register.verify.show')
                ->with('status', 'Te enviamos un código de 6 dígitos a ' . $normalizedEmail . '. Ingresa el código para completar tu registro.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('Error controlado al registrar paciente', [
                'correo' => (string) $request->input('correo', ''),
                'error' => $e->getMessage(),
            ]);

            $request->session()->forget(['pending_registration', 'pending_registration_code', 'pending_registration_expires_at']);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No pudimos completar el registro. Intenta nuevamente en unos minutos.',
                ], 503);
            }

            return back()
                ->withErrors(['general' => 'No pudimos completar el registro. Intenta nuevamente en unos minutos.'])
                ->withInput($request->only('nombre', 'correo'));
        }
    }

    public function showVerificationForm(Request $request): RedirectResponse|View
    {
        $pending = $request->session()->get('pending_registration');
        if (!$pending) {
            return redirect()->route('register.show');
        }

        return view('auth.verify-register', [
            'correo' => $pending['correo'] ?? null,
        ]);
    }

    public function verifyRegistrationCode(Request $request): RedirectResponse
    {
        try {
            $request->validate(
                [
                    'codigo' => ['required', 'digits:6'],
                ],
                [
                    'codigo.required' => 'Ingresa el código de verificación.',
                    'codigo.digits' => 'El código debe tener 6 dígitos.',
                ],
            );

            $pending = $request->session()->get('pending_registration');
            $storedCode = (string) $request->session()->get('pending_registration_code', '');
            $expiresAt = $request->session()->get('pending_registration_expires_at');

            if (!$pending || !$storedCode || !$expiresAt) {
                return $this->missingPendingRegistrationResponse($request);
            }

            try {
                $expiry = Carbon::parse((string) $expiresAt);
            } catch (Throwable $e) {
                $request->session()->forget(['pending_registration', 'pending_registration_code', 'pending_registration_expires_at']);

                return $this->missingPendingRegistrationResponse($request);
            }

            if (now()->greaterThan($expiry)) {
                $request->session()->forget(['pending_registration', 'pending_registration_code', 'pending_registration_expires_at']);

                return $this->expiredCodeResponse($request);
            }

            if ((string) $request->input('codigo') !== $storedCode) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'errors' => [
                            'codigo' => ['El código ingresado no es correcto.'],
                        ],
                    ], 422);
                }

                return back()
                    ->withErrors(['codigo' => 'El código ingresado no es correcto.'])
                    ->withInput();
            }

            $usuario = Usuario::query()->firstOrCreate(
                ['correo' => $pending['correo']],
                [
                    'rol_id' => 1,
                    'nombre' => $pending['nombre'],
                    'contrasena' => $pending['contrasena'],
                    'rol' => 'cliente',
                    'esta_activo' => true,
                    'correo_verificado_en' => now(),
                ],
            );

            $request->session()->forget(['pending_registration', 'pending_registration_code', 'pending_registration_expires_at']);

            Auth::login($usuario);
            $request->session()->regenerate();
            GuestShopperService::mergeIntoUser($request, (int) $usuario->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'Cuenta verificada correctamente.',
                    'redirect' => route('landing'),
                ]);
            }

            return redirect()->route('landing');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('Error controlado al verificar registro por PIN', [
                'correo' => (string) ($request->session()->get('pending_registration')['correo'] ?? ''),
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No pudimos validar el código en este momento. Intenta nuevamente.',
                ], 503);
            }

            return back()->withErrors([
                'general' => 'No pudimos validar el código en este momento. Intenta nuevamente.',
            ]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    private function authValidationMessages(): array
    {
        return [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'correo.max' => 'El correo no puede exceder 255 caracteres.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener mínimo 8 caracteres.',
            'contrasena.max' => 'La contraseña no puede exceder 72 caracteres.',
        ];
    }

    private function registerValidationMessages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras, espacios, guion, punto y apóstrofo.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'correo.max' => 'El correo no puede exceder 255 caracteres.',
            'correo.unique' => 'Este correo ya está registrado.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener mínimo 8 caracteres.',
            'contrasena.max' => 'La contraseña no puede exceder 72 caracteres.',
            'contrasena.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }

    private function failedAuthResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'errors' => [
                    'correo' => [$message],
                ],
            ], 422);
        }

        return back()
            ->withErrors([
                'correo' => $message,
            ])
            ->onlyInput('correo');
    }

    private function missingPendingRegistrationResponse(Request $request): RedirectResponse|JsonResponse
    {
        $message = 'No encontramos un registro pendiente. Intenta registrarte de nuevo.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 409);
        }

        return redirect()
            ->route('register.show')
            ->withErrors(['general' => $message]);
    }

    private function expiredCodeResponse(Request $request): RedirectResponse|JsonResponse
    {
        $message = 'El código expiró. Vuelve a registrarte para recibir uno nuevo.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 410);
        }

        return redirect()
            ->route('register.show')
            ->withErrors(['general' => $message]);
    }
}
