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
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->longText('description');
            $table->decimal('vehicle_coefficient',5,2);
            $table->decimal('avg_fuel_consumption',5,2);
            $table->decimal('base_fare',10,2);
            $table->decimal('min_weight', 8,2);
            $table->decimal('max_weight', 8,2);
            $table->decimal('min_length', 8,2);
            $table->decimal('max_length', 8,2);
            $table->decimal('min_width', 8,2);
            $table->decimal('max_width', 8,2);
            $table->decimal('min_height', 8,2);
            $table->decimal('max_height', 8,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
