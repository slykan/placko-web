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
        Schema::create('racuni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->foreignId('klijent_id')->constrained('klijenti')->restrictOnDelete();
            $table->string('broj')->unique();
            $table->unsignedInteger('redni_broj');
            $table->unsignedSmallInteger('godina');
            $table->date('datum_izdavanja');
            $table->time('vrijeme_izdavanja')->nullable();
            $table->date('datum_dospijeca')->nullable();
            $table->date('datum_isporuke')->nullable();
            $table->string('mjesto_izdavanja')->nullable();
            $table->string('nacin_placanja')->default('transakcijski');
            $table->text('napomena')->nullable();
            $table->string('status')->default('nacrt'); // nacrt, final
            $table->timestamp('placen_at')->nullable();
            $table->decimal('ukupno_osnovica', 10, 2)->default(0);
            $table->decimal('ukupno_rabat', 10, 2)->default(0);
            $table->decimal('ukupno_pdv', 10, 2)->default(0);
            $table->decimal('ukupno', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racuni');
    }
};
