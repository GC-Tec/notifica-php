<?php

namespace Notifica\Resources;

use Notifica\Exceptions\NotificaException;
use Notifica\Resource;

class DevicesResource extends Resource
{
    /**
     * Register a web installation. Idempotent — 409 conflicts are silently ignored.
     */
    public function registerWeb(
        string $installationKey,
        string $customerExternalId,
        ?string $name = null,
        ?string $email = null,
    ): void {
        $customer = array_filter([
            'external_id' => $customerExternalId,
            'name' => $name,
            'email' => $email,
        ], fn ($v) => $v !== null);

        try {
            $this->request('POST', 'devices/installations', [
                'json' => [
                    'platform' => 'web',
                    'push_provider' => 'web',
                    'installation_key' => $installationKey,
                    'customer' => $customer,
                ],
            ]);
        } catch (NotificaException $e) {
            if ($e->statusCode !== 409) {
                throw $e;
            }
        }
    }

    /**
     * Register a mobile (iOS or Android) device installation.
     *
     * @param  array<string>  $tags
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function registerMobile(
        string $platform,
        string $pushProvider,
        ?string $pushToken = null,
        ?string $customerExternalId = null,
        ?string $customerName = null,
        ?string $customerEmail = null,
        ?string $installationKey = null,
        ?string $code = null,
        ?array $tags = null,
        ?string $deviceFingerprint = null,
        ?array $metadata = null,
    ): array {
        $payload = array_filter([
            'platform' => $platform,
            'push_provider' => $pushProvider,
            'push_token' => $pushToken,
            'installation_key' => $installationKey,
            'code' => $code,
            'tags' => $tags,
            'device_fingerprint' => $deviceFingerprint,
            'metadata' => $metadata,
        ], fn ($v) => $v !== null);

        if ($customerExternalId !== null) {
            $payload['customer'] = array_filter([
                'external_id' => $customerExternalId,
                'name' => $customerName,
                'email' => $customerEmail,
            ], fn ($v) => $v !== null);
        }

        return $this->request('POST', 'devices/installations', ['json' => $payload]);
    }
}
