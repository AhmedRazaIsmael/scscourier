<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('customer_name');

            // Contacts
            $table->string('contact_person_1');
            $table->string('contact_no_1');
            $table->string('email_1');
            $table->text('address_1');

            $table->string('contact_person_2')->nullable();
            $table->string('contact_no_2')->nullable();
            $table->string('email_2')->nullable();
            $table->text('address_2')->nullable();

            // Others
            $table->string('ntn')->nullable();
            $table->string('website')->nullable();
            $table->date('open_date')->nullable();
            $table->string('parent_customer_code')->nullable();
            $table->string('sales_person')->nullable();
            $table->string('product')->nullable();
            $table->string('tariff_code')->nullable();
            $table->string('territory')->nullable();

            // Foreign keys
            $table->unsignedMediumInteger('country_id');
            $table->unsignedBigInteger('city_id');

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');

            // Status
            $table->boolean('status')->default(true); // true = Active

            $table->string('business_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
