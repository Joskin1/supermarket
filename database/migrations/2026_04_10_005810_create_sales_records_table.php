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
        Schema::create('sales_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('sales_import_batches')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('product_code_snapshot')->index();
            $table->string('category_snapshot')->nullable()->index();
            $table->string('product_name_snapshot');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity_sold');
            $table->decimal('total_amount', 14, 2);
            $table->date('sales_date')->index();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['batch_id', 'sales_date']);
            $table->index(['product_id', 'sales_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_records');
    }
};
