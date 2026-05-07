<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barcode_lookups', function (Blueprint $table): void {
            $table->id();
            $table->string('barcode', 64)->unique();
            $table->string('source', 50);
            $table->string('product_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('category_hint')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('looked_up_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barcode_lookups');
    }
};
