<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products ADD COLUMN IF NOT EXISTS brand VARCHAR(120) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products DROP COLUMN IF EXISTS brand');
    }
};