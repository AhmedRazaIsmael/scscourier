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
        Schema::table('export_invoice_items', function (Blueprint $table) {
            $table->string('ref_no')->nullable()->after('book_no');
            $table->decimal('sale_amount', 15, 2)->default(0)->after('rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('export_invoice_items', function (Blueprint $table) {
            //
        });
    }
};
