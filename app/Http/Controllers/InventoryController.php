<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $request->integer('category');

        $products = Product::query()
            ->with(['category', 'company', 'stock'])
            ->where('is_active', true)
            ->when($search !== '', fn ($query) => $query->where(fn ($productQuery) => $productQuery
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('sku', 'ilike', "%{$search}%")))
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->latest()
            ->get();

        $categories = Category::query()
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return view('inventory.index', [
            'categories' => $categories,
            'products' => $products,
            'search' => $search,
            'selectedCategoryId' => $categoryId,
        ]);
    }
}
