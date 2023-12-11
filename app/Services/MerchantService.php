<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Repositories\{MerchantRepository,UserRepository,ResponseRepository};
use App\Models\{User,Order,Merchant,Affiliate};
use Illuminate\Support\Facades\{Validator,Hash};
class MerchantService
{
    /**
     * @var $responseRepository object ResponseRepository
     * @var $userRepository object UserRepository
     * @var $merchantRepository object MerchantRepository
     */

    private $responseRepository;
    private $userRepository;
    private $merchantRepository;

    public function __construct(ResponseRepository $responseRepository,UserRepository $userRepository,MerchantRepository $merchantRepository){
        $this->userRepository = $userRepository;
        $this->responseRepository = $responseRepository;
        $this->merchantRepository = $merchantRepository;
    }

    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // validate the request data fields
        $validations = $this->userRepository->validations($data,[
            'domain'=>'required|unique:merchants',
            'name'=>'required',
            'email'=>'required:unique:users',
            'api_key'=>'required',
        ]);

        // if validations fails then return error in response
        if(!$validations['success']){ return $this->responseRepository->error($validations['errors']);}

        // Create a new user
        $user = $this->userRepository->store([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['api_key']), // Storing API key in the password field
            'type' => User::MERCHANT_TYPE,
        ]);

        // Create a new merchant
        $merchant = $this->merchantRepository->store([
            'user_id' => $user->id,
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);

        // send only specific property
        $user = $user->only(['id','name','email']);
        $merchant = $merchant->only(['id','display_name','domain']);
        return $this->responseRepository->success(['user'=>$user,'merchant'=>$merchant],"You have been registered successfully. Thanks!");
    }

    /**
     * Update the user
     *
     * @param User $user
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // validation rules for request
        $validations = Validator::make($data,[
            'domain'=>'required',
            'name'=>'required',
            'email'=>'required',
            'api_key'=>'required',
        ]);

        // if validation rules failed then return success false and error messages
        if($validations->fails()) { return $this->responseRepository->error($validations->errors()); }

        // Update user details
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['api_key']), // Update API key in the password field
        ]);

        // Update associated merchant details
        $user->merchant->update([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);

        return $this->responseRepository->success([],"User and merchant have been updated successfully");
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // Find the user by email
        $user = User::where('email', $email)->first();

        // If user not found, return null
        if (!$user) {
            return $this->responseRepository->error([],"User does not exist against $email");
        }

        // Return the associated merchant
        return $this->responseRepository->success(['merchant'=>$user->merchant],"User and merchant have been updated successfully");
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // Get all unpaid orders for the affiliate
        $unpaidOrders = $affiliate->orders()->where('payout_status', 'unpaid')->get();

        if($unpaidOrders->count() >0){
            // Dispatch a payout job for each unpaid order
            PayoutOrderJob::dispatch($unpaidOrders,$affiliate);
            return $this->responseRepository->success([],"Job has been dispatched against every unpain order");
        }else{
            return $this->responseRepository->success([],"There is not any order which is unpaid against affiliate");
        }
    }
}
