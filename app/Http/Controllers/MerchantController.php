<?php

namespace App\Http\Controllers;

use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Order;

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
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // dd($request->all());
        // Validate request data
        // $request->validate([
        //     'from' => 'required|date',
        //     'to' => 'required|date|after_or_equal:from',
        // ]);

        $fromDate = Carbon::parse($request->input('from'));
        $toDate = Carbon::parse($request->input('to'))->endOfDay();

        
        // Fetch orders within the date range
        $orders = Order::whereBetween('created_at', [$fromDate, $toDate])->get();
        // Calculate order statistics
        $orderCount = $orders->count();
        $commissionOwed = $orders->sum('commission_owed');
        $revenue = $orders->sum('subtotal');

        // Return the result as a JSON response
        return response()->json([
            'count' => $orderCount,
            'commission_owed' => $commissionOwed,
            'revenue' => $revenue,
        ]);
    }
}