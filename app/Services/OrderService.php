<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

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
        // Check if the order with the same order_id already exists
        if (Order::where('order_id', $data['order_id'])->exists()) {
            // Ignore duplicates
            return;
        }

        // Find or create a user based on the email
        $user = User::firstOrCreate(['email' => $data['customer_email']]);

        // Find the associated affiliate for the user
        $affiliate = $user->affiliate;

        // If affiliate not found, create a new affiliate
        if (!$affiliate) {
            // You may want to adjust the commission rate based on your business logic
            $commissionRate = 0.1; // Example commission rate
            $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

            // If the merchant is not found, you may want to handle this case according to your requirements
            if (!$merchant) {
                // Handle the case where the merchant is not found
                // You might throw an exception, log an error, or take other appropriate actions
                return;
            }

            // Create a new affiliate associated with the user
            $affiliate = $this->affiliateService->register($merchant, $user, $data['customer_name'], $commissionRate);
        }

        // Create a new order
        $order = new Order([
            'order_id' => $data['order_id'],
            'subtotal_price' => $data['subtotal_price'],
            'discount_code' => $data['discount_code'],
            'customer_email' => $data['customer_email'],
            'customer_name' => $data['customer_name'],
            'affiliate_id' => $affiliate->id,
        ]);

        // Save the order to the database
        $order->save();

        // Perform any additional processing or logging as needed
    }
}
