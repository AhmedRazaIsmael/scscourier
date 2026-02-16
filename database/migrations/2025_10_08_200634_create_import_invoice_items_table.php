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
        Schema::create('import_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_invoice_id');
            $table->string('book_no')->nullable();
            $table->string('shipper')->nullable();
            $table->string('account_head')->nullable();
            $table->string('currency')->nullable();
            $table->decimal('currency_rate', 10, 2)->nullable();
            $table->decimal('gross_weight', 10, 2)->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('freight_rate', 10, 2)->nullable();
            $table->timestamps();
                
            $table->foreign('import_invoice_id')->references('id')->on('import_invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_invoice_items');
    }
};
