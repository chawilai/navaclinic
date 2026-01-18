<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('treatment_records', function (Blueprint $table) {
            $table->dropColumn('prescription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_records', function (Blueprint $table) {
            $table->text('prescription')->nullable()->after('treatment_details');
        });
    }
};
