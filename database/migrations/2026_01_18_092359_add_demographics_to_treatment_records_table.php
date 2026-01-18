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
            $table->string('id_card_number')->nullable()->after('booking_id');
            $table->date('date_of_birth')->nullable()->after('id_card_number');
            $table->integer('age')->nullable()->after('date_of_birth');
            $table->string('gender')->nullable()->after('age');
            $table->string('race')->nullable()->after('gender');
            $table->string('nationality')->nullable()->after('race');
            $table->string('religion')->nullable()->after('nationality');
            $table->string('occupation')->nullable()->after('religion');
            $table->text('address')->nullable()->after('occupation');
            $table->string('emergency_contact_name')->nullable()->after('address');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_records', function (Blueprint $table) {
            $table->dropColumn([
                'id_card_number',
                'date_of_birth',
                'age',
                'gender',
                'race',
                'nationality',
                'religion',
                'occupation',
                'address',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        });
    }
};
