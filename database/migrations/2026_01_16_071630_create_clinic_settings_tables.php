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
        Schema::create('clinic_schedules', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week')->comment('0=Sunday, 6=Saturday');
            $table->boolean('is_open')->default(true);
            $table->time('open_time')->default('09:00:00');
            $table->time('close_time')->default('20:00:00');
            $table->timestamps();
        });

        Schema::create('clinic_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('label');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_holidays');
        Schema::dropIfExists('clinic_schedules');
    }
};
