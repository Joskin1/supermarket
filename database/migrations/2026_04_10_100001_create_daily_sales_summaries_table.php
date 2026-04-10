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
        Schema::create('daily_sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('sales_date')->unique();
            $table->unsignedInteger('total_transactions_count')->default(0);
            $table->unsignedInteger('total_quantity_sold')->default(0);
            $table->decimal('total_sales_amount', 14, 2)->default(0);
            $table->unsignedInteger('batches_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_sales_summaries');
    }
};
