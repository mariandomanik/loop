<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProductToOrderRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(): AnonymousResourceCollection
    {
        return OrderResource::collection(Order::orderBy('id')->paginate(15));
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order);
    }

    public function destroy(Order $order): JsonResponse
    {
        Order::destroy($order->id);

        return response()->json(['message' => 'Order deleted']);
    }

    public function update(Order $order): OrderResource
    {
        //there is no need to update the order, because we are not changing anything
        return new OrderResource($order);
    }

    public function store(CreateOrderRequest $request): OrderResource
    {
        $order = Order::create([
            'customer_id' => $request->get('customer_id'),
            'is_paid' => false,
        ]);

        return new OrderResource($order);
    }

    public function addProduct(AddProductToOrderRequest $request, Order $order): OrderResource
    {
        $product = $request->validated();
        $order->products()->attach($product['product_id']);

        return OrderResource::make($order);
    }

    public function pay()
    {
        return $this->orderService->pay();
    }
}
