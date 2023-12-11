<?php

namespace App\Services;

use App\Models\{Affiliate,Merchant,User,Order};
use App\Repositories\{MerchantRepository,UserRepository,OrderRepository,ResponseRepository};

class OrderService
{
    /**
     * @var $affiliateService object AffiliateService
     * @var $responseRepository object ResponseRepository
     * @var $merchantRepository object MerchantRepository
     * @var $userRepository object UserRepository
     * @var $orderRepository object OrderRepository
    */

    private $affiliateService;
    private $responseRepository;
    private $merchantRepository;
    private $userRepository;
    private $orderRepository;

    public function __construct(AffiliateService $affiliateService,ResponseRepository $responseRepository,MerchantRepository $merchantRepository,UserRepository $userRepository,OrderRepository $orderRepository){
        $this->responseRepository = $responseRepository;
        $this->affiliateService = $affiliateService;
        $this->merchantRepository = $merchantRepository;
        $this->userRepository = $userRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     */
    public function processOrder(array $data)
    {
        $orderID = $data['order_id'];
        // Check if the order with the same order_id already exists
        if (Order::where('order_id', $data['order_id'])->exists()) {
            return $this->responseRepository->error([],"Order ID $orderID has been already processed");
        }


        // fetch the merchant against merchant domain
        $merchant = $this->merchantRepository->findByDomain($data['merchant_domain']);
        if(is_null($merchant)){
            return $this->responseRepository->error([],"Merchant does not exist against ".$data['merchant_domain']);
        }

        // affiliate user if already exist
        $user = $this->userRepository->findByConditions(['email' => $data['email'],'type' => User::TYPE_AFFILIATE]);

        // Find the associated affiliate for the user
        $affiliate = $user->affiliate;

        // If affiliate not found, create a new affiliate
        if (!$affiliate) {
            $commissionRate = 0.1; // Example commission rate
            $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], $commissionRate);
            if(is_null($affiliate)){
                return $this->responseRepository->error([],'Sorry!,Affiliate could not be created');
            }
        }

        // Create a new order
        $order = $this->orderRepository->store([
            'order_id' => $data['order_id'],
            'subtotal' => $data['subtotal_price'],
            'discount_code' => $data['discount_code'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
        ]);

        return $this->responseRepository->success([],'Order has been processed and created successfully');
    }
}
