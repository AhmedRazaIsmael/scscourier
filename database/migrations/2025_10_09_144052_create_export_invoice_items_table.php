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
        Schema::create('export_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_invoice_id')->constrained('export_invoices')->onDelete('cascade');
            $table->string('book_no')->nullable();
            $table->string('consignee')->nullable();
            $table->string('account_head')->nullable();
            $table->string('currency')->nullable();
            $table->decimal('currency_rate', 10, 2)->nullable();
            $table->decimal('gross_weight', 10, 2)->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('freight_rate', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_invoice_items');
    }
};
