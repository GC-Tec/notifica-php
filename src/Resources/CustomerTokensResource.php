<?php

namespace Notifica\Resources;

use Notifica\Resource;
use Notifica\Responses\CustomerToken;

class CustomerTokensResource extends Resource
{
    /**
     * Mint a short-lived session token for a customer.
     * Used to authenticate WebSocket and inbox requests client-side.
     */
    public function mint(string $customerExternalId): CustomerToken
    {
        return CustomerToken::fromArray($this->request('POST', 'customer-tokens', [
            'json' => ['customerExternalId' => $customerExternalId],
        ]));
    }
}
