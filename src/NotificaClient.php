<?php

namespace Notifica;

use BadMethodCallException;
use GuzzleHttp\Client;
use Notifica\Resources\CustomerTokensResource;
use Notifica\Resources\DevicesResource;
use Notifica\Resources\InboxResource;
use Notifica\Resources\NotificationsResource;
use Notifica\Responses\CustomerToken;

class NotificaClient
{
    public readonly DevicesResource $devices;
    public readonly CustomerTokensResource $customerTokens;
    public readonly InboxResource $inbox;

    private readonly NotificationsResource $notifications;

    public function __construct(string $accessToken, string $baseUrl = 'https://api.notifica.dev')
    {
        $http = new Client([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->devices = new DevicesResource($http);
        $this->customerTokens = new CustomerTokensResource($http);
        $this->inbox = new InboxResource($http);
        $this->notifications = new NotificationsResource($http);
    }

    /**
     * Bootstrap a client-side web session: register the web installation and mint a
     * session token in one call. Use the returned token to authenticate the inbox and
     * the realtime connection (see {@see Realtime}).
     */
    public function startWebSession(
        string $installationKey,
        string $customerExternalId,
        ?string $name = null,
        ?string $email = null,
    ): CustomerToken {
        $this->devices->registerWeb(
            installationKey: $installationKey,
            customerExternalId: $customerExternalId,
            name: $name,
            email: $email,
        );

        return $this->customerTokens->mint($customerExternalId);
    }

    /**
     * Send a notification through all channels declared in its via() method.
     * Targets provided here take precedence over those returned by targets().
     *
     * Returns one API response entry per channel, keyed by channel name.
     *
     * @return array<string, array<string, mixed>>
     */
    public function send(Notification $notification, Target ...$targets): array
    {
        $resolvedTargets = count($targets) > 0 ? $targets : $notification->targets();
        $scheduledAt = $notification->scheduledAt();
        $baseCode = $notification->code();
        $results = [];

        foreach ($notification->via() as $channel) {
            $method = 'to' . ucfirst($channel);

            if (! method_exists($notification, $method)) {
                throw new BadMethodCallException(
                    sprintf('"%s" lists "%s" in via() but does not implement %s().', $notification::class, $channel, $method),
                );
            }

            $results[$channel] = $this->notifications->send(
                channel: $channel,
                message: $notification->$method(),
                targets: $resolvedTargets,
                scheduledAt: $scheduledAt,
                code: $baseCode ? "{$baseCode}-{$channel}" : null,
            );
        }

        return $results;
    }
}
