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
            $table->decimal('weight', 5, 2)->nullable()->after('booking_id');
            $table->decimal('height', 5, 2)->nullable()->after('weight');
            $table->string('blood_pressure')->nullable()->after('height');
            $table->text('chief_complaint')->nullable()->after('blood_pressure');
            $table->text('physical_exam')->nullable()->after('chief_complaint');
            $table->string('massage_weight')->nullable()->comment('light, medium, heavy')->after('physical_exam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_records', function (Blueprint $table) {
            $table->dropColumn([
                'weight',
                'height',
                'blood_pressure',
                'chief_complaint',
                'physical_exam',
                'massage_weight'
            ]);
        });
    }
};
