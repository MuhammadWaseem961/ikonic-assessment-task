<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Extract necessary data from the incoming request
        $data = $request->only([
            'order_id',
            'subtotal_price',
            'merchant_domain',
            'discount_code',
            'customer_email',
            'customer_name',
        ]);

        // Call the processOrder method of the OrderService
        $this->orderService->processOrder($data);

        // Return a response indicating success
        return response()->json(['message' => 'Order processed successfully']);
    }
}
