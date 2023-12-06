<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    protected ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

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
        // Find or create the user based on the email
        $user = User::firstOrCreate(['email' => $email], [
            'name' => $name,
            'password' => bcrypt(str_random(16)), // Generate a random password
            'type' => User::TYPE_AFFILIATE,
        ]);

        // Generate a discount code for the affiliate
        $discountCode = $this->apiService->createDiscountCode($merchant);

        // Create the affiliate
        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCode['code'],
        ]);

        // Send an email to the affiliate
        try {
            Mail::to($user->email)->send(new AffiliateCreated($affiliate));
        } catch (\Exception $e) {
            // Log or handle the exception as needed
            throw new AffiliateCreateException("Failed to send affiliate registration email.");
        }

        return $affiliate;
    }
}
