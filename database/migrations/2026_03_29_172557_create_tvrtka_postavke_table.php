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
        Schema::create('tvrtka_postavke', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->unique()->constrained('tvrtke')->cascadeOnDelete();

            // SMTP
            $table->string('smtp_host')->nullable();
            $table->unsignedSmallInteger('smtp_port')->default(587);
            $table->string('smtp_user')->nullable();
            $table->string('smtp_pass')->nullable();
            $table->string('smtp_sigurnost')->default('tls'); // tls, ssl, none
            $table->string('smtp_from_name')->nullable();
            $table->string('smtp_from_email')->nullable();

            // Pretplate - podsjetnici
            $table->string('pretplate_dani_upozorenja')->default('30,15,1');
            $table->text('pretplate_email_predlozak')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tvrtka_postavke');
    }
};
