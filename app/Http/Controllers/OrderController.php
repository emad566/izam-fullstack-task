<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;
use App\Http\Traits\Controller\DestroyTrait;
use App\Http\Traits\Controller\IndexTrait;
use App\Http\Traits\Controller\EditTrait;
use App\Http\Traits\Controller\ShowTrait;
use App\Http\Traits\Controller\ToggleActiveTrait;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\FilterRequest;

class OrderController extends BaseController
{
    use IndexTrait, ShowTrait, EditTrait, DestroyTrait, ToggleActiveTrait;

    function __construct()
    {
        parent::__construct(Order::class);
    }

    function index(FilterRequest $request)
    {
        return $this->indexInit($request, function ($items) use($request){
            // Only show user's own orders for regular users
            if ($request->user() && !$request->user() instanceof \App\Models\Admin) {
                $items = $items->where('user_id', $request->user()->id);
            }

            // Filter by user name (like search) - sanitized by FilterRequest
            if ($request->validated()['user_name'] ?? false) {
                $items = $items->whereHas('user', function ($query) use ($request) {
                    $query->like('name', $request->validated()['user_name']);
                });
            }

            // Filter by user names (exact match) - sanitized by FilterRequest
            if ($request->validated()['user_names'] ?? false) {
                $items = $items->whereHas('user', function ($query) use ($request) {
                    $query->whereIn('name', $request->validated()['user_names']);
                });
            }

            // Filter by user IDs - sanitized by FilterRequest
            if ($request->validated()['user_ids'] ?? false) {
                $items = $items->whereIn('user_id', $request->validated()['user_ids']);
            }

            // Filter by category names - sanitized by FilterRequest
            if ($request->validated()['category_names'] ?? false) {
                $items = $items->whereHas('orderProducts.product.category', function ($query) use ($request) {
                    $query->whereIn('name', $request->validated()['category_names']);
                });
            }

            // Filter by category IDs - sanitized by FilterRequest
            if ($request->validated()['category_ids'] ?? false) {
                $items = $items->whereHas('orderProducts.product', function ($query) use ($request) {
                    $query->whereIn('category_id', $request->validated()['category_ids']);
                });
            }

            // Filter by product names (exact match) - sanitized by FilterRequest
            if ($request->validated()['product_names'] ?? false) {
                $items = $items->whereHas('orderProducts.product', function ($query) use ($request) {
                    $query->whereIn('name', $request->validated()['product_names']);
                });
            }

            // Filter by product name (like search) - sanitized by FilterRequest
            if ($request->validated()['product_name'] ?? false) {
                $items = $items->whereHas('orderProducts.product', function ($query) use ($request) {
                    $query->like('name', $request->validated()['product_name']);
                });
            }

            return [$items];
        }, [], isListTrashed(), function ($items) {
            return [$items];
        }, null, null, ['user', 'orderProducts.product.category']);
    }

    function show($id)
    {
        return $this->showInit($id, function ($item) use ($id) {
            // Check if user can access this order
            $user = request()->user();
            if ($user && !$user instanceof \App\Models\Admin && $item->user_id !== $user->id) {
                return [false, $this->sendResponse(false, [], 'Unauthorized access to order', null, 403)];
            }

            // Load the required relations
            $item->load(['user', 'orderProducts.product']);

            return [$item];
        }, isListTrashed());
    }

    public function create(?Request $request = null)
    {
        try {
            return $this->sendResponse(true, [], trans('msg.data-to-create'), null);
        } catch (\Throwable $th) {
            return $this->sendServerError(trans('msg.technicalError'), $request->all(), $th);
        }
    }

    public function store(OrderRequest $request)
    {
        try {
            $inputs = $request->validated();

            DB::beginTransaction();

            // Set user_id from authenticated user
            $inputs['user_id'] = $request->user()->id;

            // Remove products from inputs as we'll handle them separately
            $products = $inputs['products'];
            unset($inputs['products']);

            // Calculate total amount
            $totalAmount = 0;
            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['product_id']);

                // Check if enough stock is available
                if ($product->stock < $productData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock}, Requested: {$productData['quantity']}");
                }

                $totalAmount += $product->price * $productData['quantity'];
            }

            $inputs['total_amount'] = $totalAmount;

            // Generate order number if not provided
            if (empty($inputs['order_number'])) {
                $inputs['order_number'] = $this->model::generateOrderNumber();
            }

            // Create the order
            $order = $this->model::create($inputs);

            // Create order products
            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['product_id']);

                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $product->price * $productData['quantity'],
                ]);

                // Update product stock
                $product->decrement('stock', $productData['quantity']);
            }

            DB::commit();

            return $this->sendResponse(true, [
                'item' => new $this->resource($order->load(['orderProducts.product', 'user'])),
            ], trans('Created'), null, 201, $request);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->sendServerError(trans('msg.technicalError'), $request->all(), $th);
        }
    }

    function edit($id)
    {
        return $this->editInit($id, function ($item) {
            // Load the required relations
            $item->load(['user', 'orderProducts.product']);
            return [$item];
        }, isListTrashed());
    }

    public function update(OrderRequest $request, $id)
    {
        try {
            $inputs = $request->validated();
            $order = $this->model::findOrFail($id);

            $order->update($inputs);

            return $this->sendResponse(true, [
                'item' => new $this->resource($order->refresh()),
            ], trans('msg.updated'), null, 200, $request);
        } catch (\Throwable $th) {
            return $this->sendServerError(trans('msg.technicalError'), $request->all(), $th);
        }
    }

    function destroy($id)
    {
        return $this->destroyInit($id, function ($item) {
            // Check if user can delete this order
            $user = request()->user();
            if ($user && !$user instanceof \App\Models\Admin && $item->user_id !== $user->id) {
                return [false, $this->sendResponse(false, [], 'Unauthorized access to order', null, 403)];
            }

            // Restore stock when order is deleted
            $item->load('orderProducts.product');
            foreach ($item->orderProducts as $orderProduct) {
                if ($orderProduct->product) {
                    $orderProduct->product->increment('stock', $orderProduct->quantity);
                }
            }

            return [$item];
        }, isListTrashed());
    }

    function toggleActive($id, $state)
    {
        return $this->toggleActiveInit($id, $state, null, isListTrashed());
    }
}
