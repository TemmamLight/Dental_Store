<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CustomOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderStatusEnum;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'items.product', 'items.custom_order_item'])->get();
        return OrderResource::collection($orders);
    }


    public function show($id)
    {
        try {
            $order = Order::with(['customer', 'items.product', 'items.custom_order_item'])->findOrFail($id);
            return new OrderResource($order);
        } catch (\Exception $e) {
            return response()->json(['message' => 'The Order is not found', 'error' => $e->getMessage()], 404);
        }
    }

    /**
     * Create normal Order
     */
    public function createRegularOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'  => 'required|exists:customers,id',
            'items'        => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'shipping_price' => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // create order
            $order = Order::create([
                'customer_id'    => $request->customer_id,
                'number'         => 'ORD-' . time(),
                'total_price'    => collect($request->items)->sum(fn($item) => $item['quantity'] * $item['unit_price']),
                'status'         => OrderStatusEnum::PENDING->value,
                'shipping_price' => $request->shipping_price ?? 0,
                'notes'          => $request->notes,
                'order_type'     => 'regular',
            ]);

            // add product to order
            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'The order was created successfully', 'order' => new OrderResource($order)], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while creating the order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create Custom Order
     */
    public function createCustomOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'   => 'required|exists:customers,id',
            'items'         => 'required|array',
            'items.*.merchant_name' => 'required|string|max:255',
            'items.*.product_name'  => 'required|string|max:255',
            'items.*.description'   => 'nullable|string',
            'items.*.brand'         => 'nullable|string|max:255',
            'shipping_price' => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Create Custom Order
            $order = Order::create([
                'customer_id'    => $request->customer_id,
                'number'         => 'ORD-' . time(),
                'total_price'    => 0, 
                'status'         => OrderStatusEnum::PENDING->value,
                'shipping_price' => $request->shipping_price ?? 0,
                'notes'          => $request->notes,
                'order_type'     => 'custom',
            ]);

            $totalPrice = 0;

            foreach ($request->items as $item) {
                $customOrderItem = CustomOrderItem::create([
                    'merchant_name' => $item['merchant_name'],
                    'product_name'  => $item['product_name'],
                    'description'   => $item['description'] ?? null,
                    'brand'         => $item['brand'] ?? null,
                ]);

                OrderItem::create([
                    'order_id'              => $order->id,
                    'custom_order_item_id'  => $customOrderItem->id,
                    'quantity'              => 1,
                    'unit_price'            => 0, 
                ]);
            }

            // Update the total price of the order
            $order->update(['total_price' => $totalPrice]);

            DB::commit();
            return response()->json(['message' => 'Custom order created successfully', 'order' => new OrderResource($order)], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while creating the order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Only update the request if it is on hold
     */
    public function updateOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'items'        => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'shipping_price' => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $order = Order::where('id', $id)->where('status', OrderStatusEnum::PENDING->value)->first();

            if (!$order) {
                return response()->json(['message' => 'This order cannot be modified because this order is '. $order->status], 403);
            }

            $order->update([
                'shipping_price' => $request->shipping_price ?? $order->shipping_price,
                'notes'          => $request->notes ?? $order->notes,
            ]);

            $order->items()->delete();
            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            $totalPrice = collect($request->items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);
            $order->update(['total_price' => $totalPrice]);

            DB::commit();
            return response()->json(['message' => ' order uptated successfully', 'order' => new OrderResource($order)], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while updating the order', 'error' => $e->getMessage()], 500);
        }
    }
}