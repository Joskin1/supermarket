<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_records', function (Blueprint $table) {
            $table->time('sales_time')->nullable()->index();
            $table->unsignedInteger('source_row_number')->nullable();
            $table->index(['batch_id', 'source_row_number']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_records', function (Blueprint $table) {
            $table->dropIndex(['sales_time']);
            $table->dropIndex(['batch_id', 'source_row_number']);
            $table->dropColumn(['sales_time', 'source_row_number']);
        });
    }
};
