<?php

namespace App\Services;

use App\Mail\AffiliateCreated;
use App\Models\{Affiliate,Merchant,User};
use Illuminate\Support\Facades\{Mail,Hash};
use App\Repositories\{AffiliateRepository,UserRepository,ResponseRepository};
class AffiliateService
{
    /**
     * @var $responseRepository object ResponseRepository
     * @var $userRepository object UserRepository
     * @var $affiliateRepository object AffiliateRepository
    */

    private $responseRepository;
    private $userRepository;
    private $affiliateRepository;

    public function __construct(ResponseRepository $responseRepository,UserRepository $userRepository,AffiliateRepository $affiliateRepository){
        $this->responseRepository = $responseRepository;
        $this->affiliateRepository = $affiliateRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate)
    {
        // create array of data
        $data = ['email'=>$email,'name'=>$name,'commission_rate'=>$commissionRate];
        // validate the request data fields
        $validations = $this->userRepository->validations($data,[
            'name'=>'required',
            'email'=>'required|unique:users',
            'commission_rate'=>'required',
        ]);

        // if validations fails then return error in response
        if(!$validations['success']){ return $this->responseRepository->error($validations['errors']);}

        // Create a new user
        $user = $this->userRepository->store([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('123456'),
            'type' => User::TYPE_AFFILIATE,
        ]);

        // Create a new Affiliate 
        $affiliate = $this->affiliateRepository->store([
            'user_id'=>$user->id,
            'merchant_id'=>$merchant->id,
            'commission_rate'=>$commissionRate
        ]);

        if(!is_null($affiliate)){
            // Send an email notification to the affiliate
            Mail::to($email)->send(new AffiliateCreated($affiliate));
            return $affiliate;
        }else{
            return null;
        }
    }
}
