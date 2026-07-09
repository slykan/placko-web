<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skladisne_transakcije', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->foreignId('usluga_id')->constrained('usluge')->cascadeOnDelete();
            $table->foreignId('skladiste_id')->constrained('skladista')->cascadeOnDelete();
            $table->string('tip'); // ulaz, izlaz, korekcija
            $table->decimal('kolicina', 12, 3); // signed delta: + povecava, - smanjuje
            $table->decimal('cijena', 10, 2)->nullable();
            $table->foreignId('racun_id')->nullable()->constrained('racuni')->nullOnDelete();
            $table->foreignId('primka_id')->nullable()->constrained('primke')->nullOnDelete();
            $table->foreignId('inventura_id')->nullable()->constrained('inventure')->nullOnDelete();
            $table->string('napomena')->nullable();
            $table->date('datum');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skladisne_transakcije');
    }
};
