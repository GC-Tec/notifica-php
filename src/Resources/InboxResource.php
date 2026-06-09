<?php

namespace Notifica\Resources;

use Notifica\Resource;

class InboxResource extends Resource
{
    /**
     * List inbox notifications for a customer.
     *
     * @param  'all'|'read'|'unread'  $readStatus
     * @return array<string, mixed>
     */
    public function list(
        string $customerExternalId,
        int $page = 1,
        int $perPage = 20,
        string $readStatus = 'all',
    ): array {
        return $this->request('GET', 'customer-notifications', [
            'query' => [
                'customer_external_id' => $customerExternalId,
                'page' => $page,
                'per_page' => $perPage,
                'read_status' => $readStatus,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function markAsRead(string $notificationId, string $customerExternalId): array
    {
        return $this->request('PATCH', "customer-notifications/{$notificationId}/read", [
            'json' => ['customer' => ['external_id' => $customerExternalId]],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function markAsUnread(string $notificationId, string $customerExternalId): array
    {
        return $this->request('PATCH', "customer-notifications/{$notificationId}/unread", [
            'json' => ['customer' => ['external_id' => $customerExternalId]],
        ]);
    }
}
