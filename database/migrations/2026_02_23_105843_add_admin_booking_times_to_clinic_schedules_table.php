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
        Schema::table('clinic_schedules', function (Blueprint $table) {
            $table->time('admin_booking_start_time')->default('07:00:00')->after('close_time');
            $table->time('admin_booking_end_time')->default('20:00:00')->after('admin_booking_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_schedules', function (Blueprint $table) {
            $table->dropColumn(['admin_booking_start_time', 'admin_booking_end_time']);
        });
    }
};
