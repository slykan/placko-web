<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulazni_eracuni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->string('fina_id')->nullable()->comment('Jedinstveni ID poruke od FINA-e');
            $table->string('broj_racuna')->nullable();
            $table->string('dobavljac_naziv')->nullable();
            $table->string('dobavljac_oib', 11)->nullable();
            $table->date('datum_izdavanja')->nullable();
            $table->date('datum_dospijeca')->nullable();
            $table->decimal('iznos', 12, 2)->default(0);
            $table->string('valuta', 3)->default('EUR');
            $table->enum('status', ['nova', 'pregledana', 'prihvacena', 'odbijena'])->default('nova');
            $table->text('napomena')->nullable()->comment('Razlog odbijanja ili komentar');
            $table->longText('xml')->nullable()->comment('Sirovi UBL 2.1 XML');
            $table->timestamp('primljeno_at')->nullable();
            $table->timestamps();

            $table->unique(['tvrtka_id', 'fina_id']);
            $table->index(['tvrtka_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulazni_eracuni');
    }
};
