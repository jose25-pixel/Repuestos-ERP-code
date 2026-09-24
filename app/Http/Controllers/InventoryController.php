<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $request->integer('category');
        $company = Company::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        $products = Product::query()
            ->with(['category', 'company', 'stock'])
            ->where('is_active', true)
            ->when($company, fn ($query) => $query->where('company_id', $company->id))
            ->when($search !== '', fn ($query) => $query->where(fn ($productQuery) => $productQuery
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('sku', 'ilike', "%{$search}%")
                ->orWhere('brand', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%")))
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->latest('created_at')
            ->get();

        $categories = Category::query()
            ->when($company, fn ($query) => $query->where('company_id', $company->id))
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return view('inventory.index', [
            'categories' => $categories,
            'products' => $products,
            'search' => $search,
            'selectedCategoryId' => $categoryId,
            'company' => $company,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'company', 'stock']);

        return view('inventory.show', [
            'product' => $product,
            'company' => $product->company,
        ]);
    }
}
