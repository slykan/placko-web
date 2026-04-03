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
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->string('fina_cert_putanja')->nullable()->after('racun_email_predlozak');
            $table->text('fina_cert_lozinka')->nullable()->after('fina_cert_putanja'); // encrypted
            $table->string('fis_prostor_oznaka')->default('1')->after('fina_cert_lozinka');
            $table->string('fis_uredaj_oznaka')->default('1')->after('fis_prostor_oznaka');
            $table->boolean('fiskalizacija_aktivna')->default(false)->after('fis_uredaj_oznaka');
        });
    }

    public function down(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->dropColumn([
                'fina_cert_putanja', 'fina_cert_lozinka',
                'fis_prostor_oznaka', 'fis_uredaj_oznaka', 'fiskalizacija_aktivna',
            ]);
        });
    }
};
