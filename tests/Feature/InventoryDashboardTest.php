<?php

namespace Tests\Feature;

use Tests\TestCase;

class InventoryDashboardTest extends TestCase
{
    public function test_product_catalog_is_available(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Refacciones')
            ->assertSee('Categorías');
    }
}
