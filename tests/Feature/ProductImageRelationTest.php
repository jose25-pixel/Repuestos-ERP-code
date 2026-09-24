<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductImage;
use Tests\TestCase;

class ProductImageRelationTest extends TestCase
{
    public function test_product_can_store_multiple_images_in_separate_table(): void
    {
        $company = Company::create([
            'name' => 'Repuestos Demo, C.A.',
            'tax_id' => 'J-'.uniqid('TEST9-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'pro',
            'is_active' => true,
        ]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Lavadoras',
            'description' => 'Repuestos para lavadoras.',
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'LAV-IMAGES-001',
            'barcode' => '759000000900',
            'name' => 'Motor de lavadora',
            'brand' => 'Whirlpool',
            'description' => 'Motor para lavadora.',
            'cost' => 40.00,
            'price' => 79.00,
            'currency' => 'USD',
            'tax_included' => false,
            'weight_kg' => 4.80,
            'status' => 'active',
            'warranty_days' => 180,
            'part_number' => 'WH-MOTOR-22',
            'compatible_models' => ['Whirlpool 7A'],
            'min_stock' => 3,
            'is_heavy' => false,
            'is_active' => true,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/lavadoras/motor-1.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/lavadoras/motor-2.jpg',
            'is_primary' => false,
            'sort_order' => 2,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/lavadoras/motor-3.jpg',
            'is_primary' => false,
            'sort_order' => 3,
        ]);

        $images = ProductImage::where('product_id', $product->id)->orderBy('sort_order')->get();

        $this->assertCount(3, $images);
        $this->assertSame('products/lavadoras/motor-1.jpg', $images->first()->image_path);
        $this->assertTrue($images->first()->is_primary);
        $this->assertSame(3, $images->count());
    }
}
