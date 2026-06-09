<?php

namespace Notifica;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Notifica\Exceptions\NotificaException;

abstract class Resource
{
    public function __construct(protected readonly Client $http) {}

    /**
     * @return array<string, mixed>
     */
    protected function request(string $method, string $path, array $options = []): array
    {
        try {
            $response = $this->http->request($method, $path, $options);
            $body = $response->getBody()->getContents();

            return $body !== '' ? (array) json_decode($body, associative: true) : [];
        } catch (BadResponseException $e) {
            $rawBody = $e->getResponse()->getBody()->getContents();
            $body = $rawBody !== '' ? (array) json_decode($rawBody, associative: true) : [];

            throw new NotificaException(
                message: $body['message'] ?? $e->getMessage(),
                statusCode: $e->getResponse()->getStatusCode(),
                response: $body,
                previous: $e,
            );
        }
    }
}
