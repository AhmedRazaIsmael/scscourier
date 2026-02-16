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
        Schema::table('dimensional_weight_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('dimensional_weight_id')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->json('changes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dimensional_weight_logs', function (Blueprint $table) {
            $table->dropColumn(['dimensional_weight_id', 'edited_by', 'changes']);
        });
    }
};
