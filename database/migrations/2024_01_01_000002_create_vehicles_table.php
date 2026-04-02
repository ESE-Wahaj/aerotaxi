<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->decimal('price', 8, 2);
            $table->decimal('short_base', 8, 2)->default(0);
            $table->decimal('short_per_mile', 8, 2)->default(0);
            $table->decimal('long_base', 8, 2)->default(0);
            $table->decimal('long_per_mile', 8, 2)->default(0);
            $table->integer('passengers');
            $table->integer('suitcases');
            $table->string('hand_luggage_note')->nullable();
            $table->string('image');
            $table->string('description')->nullable();
            $table->string('car_model')->nullable();
            $table->integer('sort_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
