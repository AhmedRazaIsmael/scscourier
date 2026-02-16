<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->string('book_no');
            $table->string('hub_code'); // City code from cities table (e.g. KHI)
            $table->enum('scan_type', ['arrival', 'delivery']);
            $table->string('status')->default('Scanned');
            $table->foreignId('scanned_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scans');
    }
};
