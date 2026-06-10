<?php

namespace Notifica\Resources;

use Notifica\Resource;
use Notifica\Responses\CustomerNotification;
use Notifica\Responses\InboxPage;

class InboxResource extends Resource
{
    /**
     * List inbox notifications for a customer.
     *
     * @param  'all'|'read'|'unread'  $readStatus
     */
    public function list(
        string $customerExternalId,
        int $page = 1,
        int $perPage = 20,
        string $readStatus = 'all',
    ): InboxPage {
        return InboxPage::fromArray($this->request('GET', 'customer-notifications', [
            'query' => [
                'customer_external_id' => $customerExternalId,
                'page' => $page,
                'per_page' => $perPage,
                'read_status' => $readStatus,
            ],
        ]));
    }

    public function markAsRead(string $notificationId, string $customerExternalId): CustomerNotification
    {
        return CustomerNotification::fromArray($this->request('PATCH', "customer-notifications/{$notificationId}/read", [
            'json' => ['customer' => ['external_id' => $customerExternalId]],
        ]));
    }

    public function markAsUnread(string $notificationId, string $customerExternalId): CustomerNotification
    {
        return CustomerNotification::fromArray($this->request('PATCH', "customer-notifications/{$notificationId}/unread", [
            'json' => ['customer' => ['external_id' => $customerExternalId]],
        ]));
    }
}
