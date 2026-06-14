<?php

namespace App\Services\Pagos;

use App\Models\Pago;
use App\Services\Bold\BoldLinkClient;

class BoldPasarelaPago implements PasarelaPago
{
    public function iniciar(Pago $pago): string
    {
        $cfg = (array) config('pagos.drivers.bold', []);

        $enabled = (bool) ($cfg['enabled'] ?? false);
        if (!$enabled) {
            throw new \RuntimeException('Bold no está habilitado');
        }

        $identityKey = (string) ($cfg['identity_key'] ?? '');
        if (trim($identityKey) === '') {
            throw new \RuntimeException('Falta configurar BOLD_IDENTITY_KEY');
        }

        $baseUrl = (string) ($cfg['base_url'] ?? 'https://integrations.api.bold.co');
        $timeout = (int) ($cfg['timeout_seconds'] ?? 20);
        $verifySsl = (bool) ($cfg['verify_ssl'] ?? true);

        $client = new BoldLinkClient($baseUrl, $identityKey, $timeout, $verifySsl);

        $pago->loadMissing('carrito.items');

        $items = $pago->carrito?->items;
        $desc = 'Compra en Óptica';
        if ($items && $items->isNotEmpty()) {
            $first = (string) ($items->first()->nombre_producto ?? '');
            $desc = trim($first) !== '' ? ('Compra: '.$first) : $desc;
        }

        $moneda = (string) ($pago->moneda ?: 'COP');
        $monto = (float) $pago->monto;

        $guestEmail = (string) (($pago->meta['guest']['correo'] ?? '') ?: ($pago->meta['correo'] ?? ''));

        $callbackUrl = $this->buildCallbackUrl($pago);

        $payload = [
            'amount_type' => 'CLOSE',
            'amount' => [
                'currency' => $moneda,
                'total_amount' => (int) round($monto),
                'tip_amount' => 0,
                'taxes' => [],
            ],
            'reference' => $pago->referencia,
            'description' => mb_substr($desc, 0, 100),
        ];

        if ($guestEmail !== '') {
            $payload['payer_email'] = $guestEmail;
        }

        if ($callbackUrl !== null) {
            $payload['callback_url'] = $callbackUrl;
        }

        $res = $client->createLink($payload);

        $meta = is_array($pago->meta) ? $pago->meta : [];
        $meta['bold'] = array_merge((array) ($meta['bold'] ?? []), [
            'payment_link' => $res['payment_link'],
            'url' => $res['url'],
            'callback_url' => $callbackUrl,
            'created_at' => now()->toISOString(),
        ]);

        $pago->update([
            'meta' => $meta,
            'pasarela_transaccion_id' => $res['payment_link'],
            'pasarela_estado' => 'link_created',
        ]);

        return $res['url'];
    }

    private function buildCallbackUrl(Pago $pago): ?string
    {
        $cfg = (array) config('pagos.drivers.bold', []);

        $explicit = trim((string) ($cfg['callback_url'] ?? ''));
        if ($explicit !== '') {
            return $this->appendQuery($explicit, [
                'ref' => $pago->referencia,
            ]);
        }

        $allowInsecure = (bool) ($cfg['allow_insecure_callback'] ?? false);
        $default = route('pagos.retorno', ['driver' => 'bold', 'ref' => $pago->referencia]);

        if (!$allowInsecure && !str_starts_with($default, 'https://')) {
            return null;
        }

        return $default;
    }

    private function appendQuery(string $url, array $params): string
    {
        $parts = parse_url($url);
        $query = [];
        if (isset($parts['query'])) {
            parse_str((string) $parts['query'], $query);
        }
        foreach ($params as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $query[$k] = $v;
        }

        $base = $url;
        $hashPos = strpos($base, '#');
        $fragment = '';
        if ($hashPos !== false) {
            $fragment = substr($base, $hashPos);
            $base = substr($base, 0, $hashPos);
        }

        $base = strtok($base, '?');
        $qs = http_build_query($query);

        return $base.($qs ? ('?'.$qs) : '').$fragment;
    }
}
