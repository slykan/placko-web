<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zalihe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->foreignId('usluga_id')->constrained('usluge')->cascadeOnDelete();
            $table->foreignId('skladiste_id')->constrained('skladista')->cascadeOnDelete();
            $table->decimal('kolicina', 12, 3)->default(0);
            $table->decimal('prosjecna_nabavna_cijena', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['usluga_id', 'skladiste_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zalihe');
    }
};
