<?php

namespace App\Services;

use App\Models\BloqueContenido;

class CompanyNotificationEmailsContent
{
    public const BLOCK_KEY = 'company_notification_emails';

    public static function defaults(): array
    {
        return [
            'emails' => [],
        ];
    }

    public static function load(): array
    {
        $defaults = self::defaults();

        try {
            $block = BloqueContenido::query()
                ->where('clave', self::BLOCK_KEY)
                ->where('esta_activo', true)
                ->first();
        } catch (\Throwable $e) {
            $block = null;
        }

        $data = is_array($block?->datos) ? $block->datos : [];
        $emailsRaw = is_array($data['emails'] ?? null) ? $data['emails'] : [];

        $emails = [];
        foreach ($emailsRaw as $email) {
            $normalized = mb_strtolower(trim((string) $email));
            if ($normalized === '' || !filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $emails[] = $normalized;
        }

        $emails = array_values(array_unique($emails));

        return array_replace($defaults, [
            'emails' => $emails,
        ]);
    }

    public static function upsert(array $data): void
    {
        $emailsRaw = is_array($data['emails'] ?? null) ? $data['emails'] : [];

        $emails = [];
        foreach ($emailsRaw as $email) {
            $normalized = mb_strtolower(trim((string) $email));
            if ($normalized === '' || !filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $emails[] = $normalized;
        }

        $emails = array_values(array_unique($emails));

        BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Correos de notificacion de empresa',
                'cuerpo' => null,
                'datos' => [
                    'emails' => $emails,
                ],
                'esta_activo' => true,
                'orden' => 5,
            ]
        );
    }
}
