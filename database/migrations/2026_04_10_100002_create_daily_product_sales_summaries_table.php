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
        Schema::create('daily_product_sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('sales_date');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_code_snapshot');
            $table->string('product_name_snapshot');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('category_snapshot')->nullable();
            $table->unsignedInteger('total_quantity_sold')->default(0);
            $table->decimal('total_sales_amount', 14, 2)->default(0);
            $table->unsignedInteger('transactions_count')->default(0);
            $table->timestamps();

            $table->unique(['sales_date', 'product_id']);
            $table->index('sales_date');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_product_sales_summaries');
    }
};
