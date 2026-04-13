<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();
            $table->integer('quantity_change');
            $table->unsignedInteger('previous_stock');
            $table->unsignedInteger('new_stock');
            $table->unsignedInteger('counted_stock')->nullable();
            $table->string('reason');
            $table->string('reference')->nullable()->index();
            $table->text('note')->nullable();
            $table->date('adjustment_date')->index();
            $table->foreignId('adjusted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'adjustment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
