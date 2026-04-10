<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected string $legacyUniqueIndex = 'daily_category_sales_summaries_sales_date_category_id_unique';

    protected string $snapshotUniqueIndex = 'dcss_sales_date_category_snapshot_unique';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $indexes = $this->mysqlIndexes();

            if ($indexes->contains($this->legacyUniqueIndex)) {
                DB::statement("ALTER TABLE `daily_category_sales_summaries` DROP INDEX `{$this->legacyUniqueIndex}`");
            }

            if (! $this->mysqlIndexes()->contains($this->snapshotUniqueIndex)) {
                DB::statement("ALTER TABLE `daily_category_sales_summaries` ADD UNIQUE `{$this->snapshotUniqueIndex}` (`sales_date`, `category_snapshot`)");
            }

            return;
        }

        Schema::table('daily_category_sales_summaries', function (Blueprint $table) {
            $table->dropUnique(['sales_date', 'category_id']);
            $table->unique(['sales_date', 'category_snapshot'], $this->snapshotUniqueIndex);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $indexes = $this->mysqlIndexes();

            if ($indexes->contains($this->snapshotUniqueIndex)) {
                DB::statement("ALTER TABLE `daily_category_sales_summaries` DROP INDEX `{$this->snapshotUniqueIndex}`");
            }

            if (! $this->mysqlIndexes()->contains($this->legacyUniqueIndex)) {
                DB::statement("ALTER TABLE `daily_category_sales_summaries` ADD UNIQUE `{$this->legacyUniqueIndex}` (`sales_date`, `category_id`)");
            }

            return;
        }

        Schema::table('daily_category_sales_summaries', function (Blueprint $table) {
            $table->dropUnique($this->snapshotUniqueIndex);
            $table->unique(['sales_date', 'category_id']);
        });
    }

    protected function mysqlIndexes(): Collection
    {
        return collect(DB::select('SHOW INDEX FROM `daily_category_sales_summaries`'))
            ->pluck('Key_name')
            ->unique()
            ->values();
    }
};
