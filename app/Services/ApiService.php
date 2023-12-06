<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use Illuminate\Support\Str;
use RuntimeException;

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

    public function sendPayout(string $email, float $amount)
    {
        // Add your logic for sending payouts, e.g., using a payment gateway
        // If an exception occurs during the payout process, throw a RuntimeException

        // Example: Assume a payout is successful
        // You would typically integrate with a payment API to handle payouts
        // For demonstration purposes, we'll consider it successful here
        // In a real-world scenario, replace this with your actual payout logic

        // For demonstration purposes, throwing an exception if the email contains "error"
        if (stripos($email, 'error') !== false) {
            throw new RuntimeException("Payout failed: Email contains 'error'");
        }

        // Log or perform actual payout logic here
        // ...

        // For demonstration purposes, log the successful payout
        info("Payout successful to email: $email, Amount: $amount");
    }
}
