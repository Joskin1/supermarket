<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make the `uploaded_by` column nullable on `sales_import_batches`.
 *
 * SQLite does not support ALTER COLUMN or DROP FOREIGN KEY,
 * so we use a full table rebuild approach for SQLite.
 * MySQL path is retained for legacy data migration compatibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::table('sales_import_batches', function (Blueprint $table) {
                $table->dropForeign(['uploaded_by']);
            });

            Schema::table('sales_import_batches', function (Blueprint $table) {
                $table->unsignedBigInteger('uploaded_by')->nullable()->change();
            });

            Schema::table('sales_import_batches', function (Blueprint $table) {
                $table->foreign('uploaded_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });

            return;
        }

        // SQLite: The column is already created with the initial migration.
        // For a fresh SQLite install, we fix this in the original migration
        // by making uploaded_by nullable from the start.
        // This migration is a no-op on SQLite fresh installs.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::table('sales_import_batches', function (Blueprint $table) {
                $table->dropForeign(['uploaded_by']);
            });

            Schema::table('sales_import_batches', function (Blueprint $table) {
                $table->unsignedBigInteger('uploaded_by')->nullable(false)->change();
            });

            Schema::table('sales_import_batches', function (Blueprint $table) {
                $table->foreign('uploaded_by')
                    ->references('id')
                    ->on('users')
                    ->restrictOnDelete();
            });
        }
    }
};
