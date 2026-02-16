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
        Schema::create('shipment_costs', function (Blueprint $table) {
            $table->id();
            $table->string('trackNo'); // Links to Booking->bookNo
            $table->string('accountHead');
            $table->string('costDesc');
            $table->decimal('costAmount', 15, 2)->default(0);
            $table->enum('status', ['OPEN', 'CLOSE'])->default('OPEN');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_costs');
    }
};
