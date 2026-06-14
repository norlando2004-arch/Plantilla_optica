<?php

return [
    // Driver actual de pasarela. Por defecto: dummy (simulación local).
    'driver' => env('PAGOS_DRIVER', 'dummy'),

    // Puedes añadir credenciales por driver aquí luego.
    'drivers' => [
        'dummy' => [
            'enabled' => env('PAGOS_DUMMY_ENABLED', true),
        ],

        'bold' => [
            'enabled' => env('PAGOS_BOLD_ENABLED', false),

            // Credenciales Bold (API Link de pagos)
            'identity_key' => env('BOLD_IDENTITY_KEY', ''),

            // Base URL de integraciones (Link de pagos).
            'base_url' => env('BOLD_BASE_URL', 'https://integrations.api.bold.co'),
            'timeout_seconds' => (int) env('BOLD_TIMEOUT_SECONDS', 20),
            'verify_ssl' => (bool) env('BOLD_VERIFY_SSL', true),

            // Webhook
            // En sandbox el secreto puede venir vacío.
            'webhook_secret' => env('BOLD_WEBHOOK_SECRET', ''),
            'allow_unsigned_webhooks' => (bool) env('BOLD_ALLOW_UNSIGNED_WEBHOOKS', false),

            // Callback/retorno
            // Si estás en local (http://127.0.0.1) puedes habilitar allow_insecure_callback.
            'callback_url' => env('BOLD_CALLBACK_URL', ''),
            'allow_insecure_callback' => (bool) env('BOLD_ALLOW_INSECURE_CALLBACK', false),
        ],
    ],
];
