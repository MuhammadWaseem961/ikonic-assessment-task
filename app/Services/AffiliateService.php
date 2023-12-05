<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     * @throws AffiliateCreateException
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // Create a new Affiliate instance
        $affiliate = new Affiliate();

        // Set affiliate properties
        $affiliate->merchant_id = $merchant->id;
        $affiliate->email = $email;
        $affiliate->name = $name;
        $affiliate->commission_rate = $commissionRate;

        // Save the affiliate to the database
        try {
            $affiliate->save();
        } catch (\Exception $e) {
            // Handle database save exception
            throw new AffiliateCreateException('Failed to create affiliate.', $e->getCode(), $e);
        }

        // Send an email notification to the affiliate
        try {
            Mail::to($email)->send(new AffiliateCreated($affiliate));
        } catch (\Exception $e) {
            // Handle email send exception
            // You may log the exception or take other appropriate actions
        }

        return $affiliate;
    }
}
