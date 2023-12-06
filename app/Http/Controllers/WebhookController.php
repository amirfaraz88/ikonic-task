<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected OrderService $orderService;

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
        // Validate the incoming request data (modify as needed)
        $request->validate([
            'order_id' => 'required|string',
            'subtotal_price' => 'required|numeric',
            'merchant_domain' => 'required|string',
            'discount_code' => 'nullable|string',
            'customer_email' => 'required|email',
            'customer_name' => 'required|string',
            'status' => 'required|string|in:paid,shipped,cancelled',
        ]);

        // Extract data from the request
        $data = $request->only([
            'order_id', 'subtotal_price', 'merchant_domain',
            'discount_code', 'customer_email', 'customer_name'
        ]);
        $status = $request->input('status');

        // Process the order
        $this->orderService->processOrder($data, $status);

        // Return a JSON response indicating successful processing
        return response()->json(['message' => 'Order processed successfully']);
    }
}
