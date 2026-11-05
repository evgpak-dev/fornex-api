<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class FornexClient
{
    private const BASE_URL = 'https://fornex.com/api';

    public function __construct(
        private readonly string $apiKey
    ) {}

    /**
     * Executes an HTTP request to the Fornex API
     *
     * @param string $method GET, POST, PUT, DELETE
     * @param string $endpoint E.g., '/account/balance'
     * @param array|null $payload Request body for POST/PUT
     * @return array Decoded JSON response
     * @throws RuntimeException
     */
    public function request(string $method, string $endpoint, ?array $payload = null): array
    {
        $url = self::BASE_URL . '/' . ltrim($endpoint, '/');

        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Api-Key ' . $this->apiKey,
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        if ($payload !== null && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $rawResponse = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);

        unset($ch);

        if ($rawResponse === false) {
            throw new RuntimeException("Fornex cURL Error: {$curlError}");
        }

        $decoded = json_decode((string) $rawResponse, true);

        if ($httpCode >= 400) {
            $errorMessage = $decoded['message'] ?? $decoded['detail'] ?? "HTTP error {$httpCode}";
            throw new RuntimeException("Fornex API Error ({$httpCode}): {$errorMessage}");
        }

        return $decoded ?? [];
    }

    public function getBalance(): array
    {
        return $this->request('GET', '/account/balance');
    }

    public function getAccountInfo(): array
    {
        return $this->request('GET', '/account/info');
    }

    public function getInvoices(): array
    {
        return $this->request('GET', '/account/invoices');
    }

    public function getOrders(): array
    {
        return $this->request('GET', '/orders/');
    }

    public function getStatus(int $orderId): array
    {
        return $this->request('GET', '/vps/' . $orderId . '/status/');
    }

    public function startVPS(int $orderId): array
    {
        return $this->request('POST', '/vps/' . $orderId . '/start/');
    }

    public function stopVPS(int $orderId): array
    {
        return $this->request('POST', '/vps/' . $orderId . '/stop/');
    }
}
