<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceRecoveriesTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_recoveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('export_invoices')->onDelete('cascade');
            $table->string('invoice_type')->default('export'); // 'export' or 'import'
            $table->string('recovery_person');
            $table->string('receiving_path');
            $table->decimal('recovery_amount', 15, 2);
            $table->text('remarks')->nullable();
            $table->string('insert_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_recoveries');
    }
}
