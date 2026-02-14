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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('badge_id')
                ->constrained('badges')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('mother_last_name');
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('national_number');
            $table->string('governorate');
            $table->string('city');
            $table->string('neighborhood');
            $table->string('gender');
            $table->string('additional_phone_number')->nullable();
            $table->string('personal_picture');
            $table->string('nationality');
            $table->unsignedInteger('continuous_successful_shipments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
