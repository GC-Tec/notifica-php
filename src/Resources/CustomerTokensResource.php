<?php

namespace Notifica\Resources;

use Notifica\Resource;

class CustomerTokensResource extends Resource
{
    /**
     * Mint a short-lived session token for a customer.
     * Used to authenticate WebSocket and inbox requests client-side.
     *
     * @return array{token: string, expiresAt: string}
     */
    public function mint(string $customerExternalId): array
    {
        return $this->request('POST', 'customer-tokens', [
            'json' => ['customerExternalId' => $customerExternalId],
        ]);
    }
}
