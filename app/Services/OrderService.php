<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    protected AffiliateService $affiliateService;

    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // Check if the order_id already exists to avoid duplicates
        if (Order::where('order_id', $data['order_id'])->exists()) {
            return;
        }

        // Find or create the merchant based on the provided domain
        $merchant = Merchant::firstOrCreate(['domain' => $data['merchant_domain']]);

        // Find or create the user based on the customer_email
        $user = User::firstOrCreate(['email' => $data['customer_email']], [
            'name' => $data['customer_name'],
            'password' => bcrypt(str_random(16)), // Generate a random password
            'type' => User::TYPE_AFFILIATE,
        ]);

        // Find or create the affiliate based on the user
        $affiliate = $this->affiliateService->findOrCreateAffiliate($user, $merchant, $data['discount_code']);

        // Create the order
        $order = Order::create([
            'order_id' => $data['order_id'],
            'subtotal' => $data['subtotal_price'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
        ]);

        // Dispatch a job to handle the payout for this order
        PayoutOrderJob::dispatch($order);
    }
}
