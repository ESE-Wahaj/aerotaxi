<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('from_location');
            $table->string('to_location');
            $table->date('depart_date');
            $table->string('depart_time')->nullable();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('passenger_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('flight_number')->nullable();
            $table->text('note_to_driver')->nullable();
            $table->string('country_code')->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_id')->nullable();
            $table->decimal('total_price', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
