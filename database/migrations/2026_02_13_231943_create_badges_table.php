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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('level');
            $table->string(column: 'name');
            $table->longText(column: 'text');
            $table->unsignedInteger('continuous_successful_shipments_condition');
            $table->unsignedTinyInteger('successful_shipments_percentage_condition');
            $table->unsignedInteger('continuous_failed_shipments_condition');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
