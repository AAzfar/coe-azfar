<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private const SORTABLE_FIELDS = ['created_at', 'total', 'status'];

    /**
     * GET /api/orders
     *
     * Query: search (customer name or email), status, sort, page, per_page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            ...$this->listRules(self::SORTABLE_FIELDS),
            'status' => ['sometimes', Rule::in(Order::STATUSES)],
        ]);

        $orders = Order::query()
            ->withCount('items')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $pattern = $this->likePattern($search);
                $query->where(fn (Builder $query) => $query
                    ->whereRaw("lower(customer_name) like ? escape '\\'", [$pattern])
                    ->orWhereRaw("lower(customer_email) like ? escape '\\'", [$pattern]));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));

        return OrderResource::collection(
            $this->applySort($orders, $filters['sort'] ?? null, '-created_at')
                ->paginate($filters['per_page'] ?? self::DEFAULT_PER_PAGE)
                ->withQueryString(),
        );
    }

    /**
     * POST /api/orders
     *
     * Places an order and takes the ordered quantities out of stock in one transaction.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($data): Order {
            $products = Product::query()
                ->whereIn('id', array_column($data['items'], 'product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $stockErrors = [];
            foreach ($data['items'] as $index => $item) {
                $product = $products[$item['product_id']];
                if ($product->stock < $item['quantity']) {
                    $stockErrors["items.{$index}.quantity"] = "Only {$product->stock} of {$product->name} left in stock.";
                }
            }

            if ($stockErrors) {
                throw ValidationException::withMessages($stockErrors);
            }

            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'status' => 'pending',
            ]);

            $totalInCents = 0;
            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];
                $product->decrement('stock', $item['quantity']);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                ]);
                $totalInCents += (int) round($product->price * 100) * $item['quantity'];
            }

            $order->update(['total' => $totalInCents / 100]);

            return $order;
        });

        return OrderResource::make($order->load('items.product'))->response()->setStatusCode(201);
    }

    /**
     * GET /api/orders/{order}
     */
    public function show(Order $order): OrderResource
    {
        return OrderResource::make($order->load('items.product'));
    }
}
