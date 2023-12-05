<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @param ApiService $apiService
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        try {
            // Assuming there's a sendPayout method in the ApiService
            $result = $apiService->sendPayout($this->order->customer_email, $this->order->commission_amount);

            // If the payout is successful, update the order status to paid
            if ($result['status'] === 'success') {
                DB::transaction(function () {
                    $this->order->update(['paid' => true]);
                });
            } else {
                // Payout failed, log the error
                Log::error('Failed to send payout', ['order_id' => $this->order->id, 'error' => $result['error']]);
            }
        } catch (RuntimeException $exception) {
            // Handle the exception as needed (log, notify, etc.)
            // The order status remains unpaid in case of an exception
            report($exception);
        }
    }
}

