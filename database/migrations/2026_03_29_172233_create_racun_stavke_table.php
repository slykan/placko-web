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
        Schema::create('racun_stavke', function (Blueprint $table) {
            $table->id();
            $table->foreignId('racun_id')->constrained('racuni')->cascadeOnDelete();
            $table->foreignId('usluga_id')->nullable()->constrained('usluge')->nullOnDelete();
            $table->string('naziv');
            $table->string('opis')->nullable();
            $table->string('jedinica_mjere')->nullable()->default('kom');
            $table->decimal('kolicina', 10, 3)->default(1);
            $table->decimal('cijena', 10, 2)->default(0);
            $table->decimal('rabat_posto', 5, 2)->default(0);
            $table->decimal('pdv_stopa', 5, 2)->nullable();
            $table->decimal('ukupno', 10, 2)->default(0);
            $table->unsignedTinyInteger('redni_broj')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racun_stavke');
    }
};
