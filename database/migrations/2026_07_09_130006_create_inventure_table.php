<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->foreignId('skladiste_id')->constrained('skladista')->restrictOnDelete();
            $table->date('datum');
            $table->text('napomena')->nullable();
            $table->string('status')->default('u_tijeku'); // u_tijeku, zavrsena
            $table->timestamp('zavrsena_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inventura_stavke', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventura_id')->constrained('inventure')->cascadeOnDelete();
            $table->foreignId('usluga_id')->constrained('usluge')->restrictOnDelete();
            $table->decimal('ocekivana_kolicina', 12, 3)->default(0);
            $table->decimal('stvarna_kolicina', 12, 3)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventura_stavke');
        Schema::dropIfExists('inventure');
    }
};
