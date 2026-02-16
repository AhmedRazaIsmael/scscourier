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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('bookNo')->nullable();
            $table->date('bookDate')->nullable();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('bookingType', ['domestic','export','import','cross_border'])->nullable();
            $table->enum('service', ['document','express']);
            $table->enum('bookChannel', ['facebook','whatsapp','instagram','others'])->nullable();
            $table->enum('paymentMode', ['cod','non_cod'])->nullable();
            $table->string('origin')->nullable();
            $table->string('originCountry')->nullable();
            $table->string('destination')->nullable();
            $table->string('destinationCountry')->nullable();
            $table->string('postalCode')->nullable();
            $table->string('invoiceValue')->nullable();
            $table->string('weight')->nullable();
            $table->string('pieces')->nullable();
            $table->string('length')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->string('dimensionalWeight')->nullable();
            $table->string('orderNo')->nullable();
            $table->enum('arrivalClearance', ['actual','do','console']);
            $table->string('itemContent')->nullable();
            $table->longText('itemDetail')->nullable();
            $table->string('shipperCompany')->nullable();
            $table->string('shipperName')->nullable();
            $table->string('shipperNumber')->nullable();
            $table->string('shipperEmail')->nullable();
            $table->longText('shipperAddress')->nullable();
            $table->string('consigneeCompany')->nullable();
            $table->string('consigneeName')->nullable();
            $table->string('consigneeNumber')->nullable();
            $table->string('consigneeEmail')->nullable();
            $table->longText('consigneeAddress')->nullable();
            $table->longText('remarks')->nullable();
            $table->longText('pickupInstructions')->nullable();
            $table->longText('deliveryInstructions')->nullable();
            $table->longText('codAmount')->nullable();
            $table->longText('territory')->nullable();
            $table->foreignId('salesPerson')->constrained('users');
            $table->string('rateType')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
