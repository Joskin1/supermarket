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
        Schema::create('sales_import_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('sales_import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('raw_row');
            $table->json('error_messages');
            $table->string('product_code')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->date('sales_date')->nullable()->index();
            $table->timestamps();

            $table->index(['batch_id', 'row_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_import_failures');
    }
};
