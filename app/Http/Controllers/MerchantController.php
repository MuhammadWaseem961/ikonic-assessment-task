<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    protected $merchantService;

    public function __construct(MerchantService $merchantService)
    {
        $this->merchantService = $merchantService;
    }

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date, and possibly a merchant email
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // Extract the 'from' and 'to' dates from the request
        $fromDate = Carbon::parse($request->input('from'));
        $toDate = Carbon::parse($request->input('to'));

        // You might use an email or other identifier to fetch the merchant
        $merchantEmail = $request->input('merchant_email');
        $merchant = $this->merchantService->findMerchantByEmail($merchantEmail);

        if (!$merchant) {
            return response()->json(['error' => 'Merchant not found'], 404);
        }

        // Eager load orders and affiliates for the merchant
        $merchant->load(['orders', 'orders.affiliate']);

        // Filter orders within the specified date range
        $filteredOrders = $merchant->orders
            ->where('created_at', '>=', $fromDate)
            ->where('created_at', '<=', $toDate);

        // Calculate order statistics
        $orderCount = $filteredOrders->count();
        $unpaidCommissions = $filteredOrders
            ->where('paid', false)
            ->sum('commission_amount');
        $revenue = $filteredOrders->sum('subtotal_price');

        // Return the statistics as a JSON response
        return response()->json([
            'count' => $orderCount,
            'commission_owed' => $unpaidCommissions,
            'revenue' => $revenue,
        ]);
    }
}
