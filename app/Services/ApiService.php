<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use Illuminate\Support\Str;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Http;
use Config;
/**
 * You don't need to do anything here. This is just to help
 */
class ApiService
{

    /**
     * @var $userRepository object userRepository
     */

     private $userRepository;

     public function __construct(UserRepository $userRepository){
         $this->userRepository = $userRepository;
     }

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
    public function sendPayout(string $email, float $amount):array
    {
        $stripeSK = Config::get('app.stripe_sk');
        $user = $this->userRepository->findByEmail($email);
        $affiliate = $user->affiliate;
        $stripeConnectedAccountID = $affiliate!=null?$affiliate->stripe_connected_account_id:'';
        if($stripeConnectedAccountID!=''){
            $stripe = new \Stripe\StripeClient($stripeSK);

            $transfer = $stripe->transfers->create([
                'amount' => $amount*100,
                'currency' => 'usd',
                'destination' =>$stripeConnectedAccountID
            ]);

            if(isset($transfer->id)){
                return ['success' => true, 'message' => 'Payout successful'];
            }else{
                return ['success' => false, 'error' => 'something went wrong'];
            }
        }
        return ['success' => false, 'error' => 'Stripe connected account id is empty'];
    }
}
