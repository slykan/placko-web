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
        Schema::create('pretplata_stavke', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pretplata_id')->constrained('pretplate')->cascadeOnDelete();
            $table->foreignId('usluga_id')->nullable()->constrained('usluge')->nullOnDelete();
            $table->string('naziv')->nullable();
            $table->string('opis')->nullable();
            $table->decimal('kolicina', 10, 3)->default(1);
            $table->decimal('cijena', 10, 2)->default(0);
            $table->decimal('pdv_stopa', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pretplata_stavke');
    }
};
