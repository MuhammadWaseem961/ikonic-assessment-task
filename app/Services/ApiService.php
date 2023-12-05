<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

/**
 * You don't need to do anything here. This is just to help
 */
class ApiService
{
    /**
     * Create a new discount code for an affiliate
     *
     * @param Merchant $merchant
     *
     * @return array{id: int, code: string}
     */
    public function createDiscountCode(Merchant $merchant): array
    {
        return [
            'id' => rand(0, 100000),
            'code' => Str::uuid()
        ];
    }

    /**
     * Send a payout to an email using PayPal API
     *
     * @param  string $email
     * @param  float $amount
     * @return array
     */
    public function sendPayout(string $email, float $amount): array
    {
        // Dummy PayPal API endpoint (replace with the actual endpoint)
        $apiEndpoint = 'https://api.paypal.com/payouts';

        // Dummy PayPal API credentials (replace with your actual credentials)
        $clientId = 'your_client_id';
        $clientSecret = 'your_client_secret';

        // Dummy request payload (replace with the actual payload)
        $requestPayload = [
            'recipient_email' => $email,
            'amount' => $amount,
            // Other required fields...
        ];

        // Make a cURL request to PayPal API using Laravel's HTTP client
        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->post($apiEndpoint, $requestPayload);

        // Check the response status and handle success or failure accordingly
        if ($response->successful()) {
            // Payout successful
            // You might want to log the transaction or perform other actions
            return ['status' => 'success', 'message' => 'Payout successful'];
        } else {
            // Payout failed
            // You might want to log the error or return specific error details
            return ['status' => 'error', 'message' => 'Failed to send payout', 'error' => $response->json()];
        }
    }
}
