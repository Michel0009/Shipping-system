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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('weight', 5, 2);
            $table->decimal('height', 5, 2);
            $table->decimal('width', 5, 2);
            $table->decimal('length', 5, 2);
            $table->string('object');
            $table->boolean('insurance');
            $table->decimal('start_position_lat', 10, 8);
            $table->decimal('start_position_lng', 11, 8);
            $table->decimal('end_position_lat', 10, 8);
            $table->decimal('end_position_lng', 11, 8);
            $table->string('start_location_details');
            $table->string('end_location_details');
            $table->unsignedBigInteger('max_price')->nullable();
            $table->unsignedBigInteger('min_price')->nullable();
            $table->date('last_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
