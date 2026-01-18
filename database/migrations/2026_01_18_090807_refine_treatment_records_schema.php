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
            $table->dropColumn(['next_appointment', 'body_element']);
            $table->renameColumn('smoking_drinking', 'accident_history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_records', function (Blueprint $table) {
            $table->date('next_appointment')->nullable();
            $table->string('body_element')->nullable();
            $table->renameColumn('accident_history', 'smoking_drinking');
        });
    }
};
