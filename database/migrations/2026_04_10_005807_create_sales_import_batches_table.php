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
        Schema::create('sales_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique();
            $table->string('file_name');
            $table->string('file_path')->nullable();
            $table->string('original_file_name')->nullable();
            $table->string('file_hash', 64)->index();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('uploaded')->index();
            $table->date('sales_date_from')->nullable()->index();
            $table->date('sales_date_to')->nullable()->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->unsignedInteger('total_quantity_sold')->default(0);
            $table->decimal('total_sales_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_import_batches');
    }
};
