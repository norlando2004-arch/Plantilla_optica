<?php

namespace App\Services\Bold;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class BoldLinkClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $identityKey,
        private readonly int $timeoutSeconds = 20,
        private readonly bool $verifySsl = true,
    ) {
    }

    private function http(): PendingRequest
    {
        $request = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withHeaders([
                'Authorization' => 'x-api-key '.$this->identityKey,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeoutSeconds);

        if (!$this->verifySsl) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    /**
     * Crea un link de pago (monto cerrado) y retorna ['payment_link' => 'LNK_...', 'url' => 'https://checkout.bold.co/...']
     */
    public function createLink(array $payload): array
    {
        $resp = $this->http()->post('/online/link/v1', $payload);

        if (!$resp->successful()) {
            throw new \RuntimeException('Bold createLink failed: HTTP '.$resp->status().' '.$resp->body());
        }

        $json = $resp->json();
        $pl = $json['payload']['payment_link'] ?? null;
        $url = $json['payload']['url'] ?? null;

        if (!is_string($pl) || $pl === '' || !is_string($url) || $url === '') {
            throw new \RuntimeException('Bold createLink unexpected response: '.json_encode($json));
        }

        return ['payment_link' => $pl, 'url' => $url];
    }

    /**
     * Consulta el estado/datos de un link de pago.
     */
    public function getLink(string $paymentLink): array
    {
        $paymentLink = trim($paymentLink);
        if ($paymentLink === '') {
            throw new \InvalidArgumentException('paymentLink is required');
        }

        $resp = $this->http()->get('/online/link/v1/'.urlencode($paymentLink));

        if (!$resp->successful()) {
            throw new \RuntimeException('Bold getLink failed: HTTP '.$resp->status().' '.$resp->body());
        }

        $json = $resp->json();
        if (!is_array($json)) {
            throw new \RuntimeException('Bold getLink unexpected response');
        }

        return $json;
    }
}
