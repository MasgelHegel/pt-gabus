<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Portal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'category_id']);
        $perPage = min((int) $request->input('per_page', 20), 100);

        $products = $this->productService->paginatePublic($filters, $perPage);

        return ApiResponse::success([
            'data' => $products->map(fn ($p) => [
                'id'          => $p->id,
                'sku'         => $p->sku,
                'name'        => $p->name,
                'unit'        => $p->unit,
                'sell_price'  => (float) $p->sell_price,
                'stock'       => $p->stock,
                'image_url'   => $p->image_url,
                'category'    => $p->category?->name,
                'description' => $p->description,
            ]),
            'meta' => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findById($id);

        return ApiResponse::success([
            'id'          => $product->id,
            'sku'         => $product->sku,
            'name'        => $product->name,
            'unit'        => $product->unit,
            'sell_price'  => (float) $product->sell_price,
            'stock'       => $product->stock,
            'image_url'   => $product->image_url,
            'category'    => $product->category?->name,
            'description' => $product->description,
        ]);
    }
}
