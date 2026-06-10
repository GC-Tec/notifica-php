<?php

namespace Notifica;

/**
 * Client-side realtime contract for the customer inbox.
 *
 * Connect a Socket.IO client to `{baseUrl}` + {@see Realtime::NAMESPACE}, authenticated
 * with a token minted via {@see NotificaClient::startWebSession()} (or `customerTokens->mint()`),
 * and refetch the inbox whenever the {@see Realtime::EVENT_INBOX_UPDATED} event fires.
 */
final class Realtime
{
    /** Socket.IO namespace for customer-scoped events. */
    public const NAMESPACE = '/customer-events';

    /** Emitted when a customer's inbox changes (new notification, read-state change, …). */
    public const EVENT_INBOX_UPDATED = 'inbox.updated';
}
