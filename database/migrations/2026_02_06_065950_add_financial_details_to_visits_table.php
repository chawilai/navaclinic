<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->decimal('treatment_fee', 10, 2)->nullable()->default(0)->after('price');
            $table->string('discount_type')->default('amount')->after('treatment_fee');
            $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
            $table->decimal('tip', 10, 2)->default(0)->after('discount_value');
        });

        // Copy existing price to treatment_fee
        DB::statement('UPDATE visits SET treatment_fee = price');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['treatment_fee', 'discount_type', 'discount_value', 'tip']);
        });
    }
};
