<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barcode_lookup_caches', function (Blueprint $table): void {
            $table->id();
            $table->string('barcode')->unique();
            $table->string('provider')->index();
            $table->string('product_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('category_hint')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_found_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_lookup_caches');
    }
};
