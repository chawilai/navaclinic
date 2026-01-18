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
            // Vital Signs additions
            $table->decimal('temperature', 4, 1)->nullable()->after('blood_pressure');
            $table->integer('pulse_rate')->nullable()->after('temperature');
            $table->integer('respiratory_rate')->nullable()->after('pulse_rate');

            // History fields
            $table->text('underlying_disease')->nullable()->after('booking_id');
            $table->text('surgery_history')->nullable()->after('underlying_disease');
            $table->text('drug_allergy')->nullable()->after('surgery_history');
            $table->text('smoking_drinking')->nullable()->after('drug_allergy');

            // Other
            $table->string('body_element')->nullable()->after('physical_exam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_records', function (Blueprint $table) {
            $table->dropColumn([
                'temperature',
                'pulse_rate',
                'respiratory_rate',
                'underlying_disease',
                'surgery_history',
                'drug_allergy',
                'smoking_drinking',
                'body_element'
            ]);
        });
    }
};
