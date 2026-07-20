<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('business_logo_path')->nullable()->after('business_name');
            $table->string('business_type')->nullable()->after('business_logo_path');
            $table->string('business_phone')->nullable()->after('low_stock_contact_email');
            $table->string('business_email')->nullable()->after('business_phone');
            $table->text('business_address')->nullable()->after('business_email');
            $table->timestamp('business_profile_completed_at')->nullable()->after('receipt_footer');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'business_logo_path',
                'business_type',
                'business_phone',
                'business_email',
                'business_address',
                'business_profile_completed_at',
            ]);
        });
    }
};
