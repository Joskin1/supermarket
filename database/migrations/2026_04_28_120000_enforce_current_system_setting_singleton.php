<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('singleton_key')->nullable();
        });

        $rows = DB::table('system_settings')
            ->orderBy('id')
            ->get(['id']);

        $firstId = $rows->first()->id ?? null;

        foreach ($rows as $row) {
            DB::table('system_settings')
                ->where('id', $row->id)
                ->update([
                    'singleton_key' => $row->id === $firstId
                        ? 'current'
                        : 'legacy-'.$row->id,
                ]);
        }

        Schema::table('system_settings', function (Blueprint $table) {
            $table->unique('singleton_key');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropUnique(['singleton_key']);
            $table->dropColumn('singleton_key');
        });
    }
};
