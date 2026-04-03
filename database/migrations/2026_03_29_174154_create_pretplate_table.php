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
        Schema::create('pretplate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->foreignId('klijent_id')->constrained('klijenti')->restrictOnDelete();
            $table->string('period')->default('godisnje'); // mjesecno, tromjesecno, polugodisnje, godisnje
            $table->date('datum_pocetka');
            $table->date('datum_isteka');
            $table->string('status')->default('aktivna'); // aktivna, neaktivna, istekla
            $table->text('opis')->nullable();
            $table->decimal('ukupno', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pretplate');
    }
};
