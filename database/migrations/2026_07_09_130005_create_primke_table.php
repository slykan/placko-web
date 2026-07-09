<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('primke', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->foreignId('dobavljac_id')->nullable()->constrained('dobavljaci')->nullOnDelete();
            $table->foreignId('skladiste_id')->constrained('skladista')->restrictOnDelete();
            $table->string('broj');
            $table->unsignedInteger('redni_broj');
            $table->unsignedSmallInteger('godina');
            $table->date('datum_primke');
            $table->text('napomena')->nullable();
            $table->decimal('ukupno', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['tvrtka_id', 'broj']);
            $table->unique(['tvrtka_id', 'godina', 'redni_broj']);
        });

        Schema::create('primka_stavke', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primka_id')->constrained('primke')->cascadeOnDelete();
            $table->foreignId('usluga_id')->constrained('usluge')->restrictOnDelete();
            $table->decimal('kolicina', 12, 3)->default(1);
            $table->decimal('nabavna_cijena', 10, 2)->default(0);
            $table->decimal('ukupno', 10, 2)->default(0);
            $table->unsignedTinyInteger('redni_broj')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('primka_stavke');
        Schema::dropIfExists('primke');
    }
};
