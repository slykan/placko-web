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
        Schema::create('usluge', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->string('naziv');
            $table->string('jedinica_mjere')->nullable()->default('kom');
            $table->decimal('cijena', 10, 2)->default(0);
            $table->decimal('pdv_stopa', 5, 2)->nullable()->comment('null = bez PDV-a, 0/5/13/25');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usluge');
    }
};
