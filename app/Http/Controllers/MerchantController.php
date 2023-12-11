<?php

namespace App\Http\Controllers;

use App\Repositories\{ResponseRepository};
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Validator;

class MerchantController extends Controller
{
    /**
     * @var $merchantService object MerchantService
     * @var $responseRepository object ResponseRepository
    */

    private $merchantService;
    private $responseRepository;

    public function __construct(MerchantService $merchantService,ResponseRepository $responseRepository){
        $this->responseRepository = $responseRepository;
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
        // validation rules for request
        $validations = Validator::make($request->only(['to','from','merchant_email']),[
            'merchant_email'=>'required|email',
            'to'=>'required|date',
            'from'=>'required|date',
        ]);

        // if validation rules failed then return success false and error messages
        if($validations->fails()) { return $this->responseRepository->error($validations->errors()); }

        // find the merchant against email
        $merchant = $this->merchantService->findMerchantByEmail($request->merchant_email);

        if (!$merchant) {
            return $this->responseRepository->error([],'Merchant does not exist against '.$request->merchant_email);
        }

        // Filter orders within the specified date range
        $filteredOrders = $merchant->orders()
            ->where('created_at', '>=', Carbon::parse($request->from))
            ->where('created_at', '<=', Carbon::parse($request->to));

        // Calculate order statistics
        $ordersCount = $filteredOrders->count();
        // commission with an affiliate
        $unpaidCommissions = $merchant->affiliate->orders()
            ->where('created_at', '>=', Carbon::parse($request->from))
            ->where('created_at', '<=', Carbon::parse($request->to))
            ->where('payout_status', 'unpaid')
            ->sum('commission_owed');

        // total revenue of orders within range
        $revenue = $filteredOrders->sum('subtotal');

        // Return the statistics as a JSON response
        return $this->responseRepository->success([
            'count' => $ordersCount,
            'commission_owed' => $unpaidCommissions,
            'revenue' => $revenue,
        ]);

    }
}
