<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->boolean('eracun_aktivan')->default(false)->after('fiskalizacija_aktivna');
            $table->string('eracun_cert_putanja')->nullable()->after('eracun_aktivan');
            $table->text('eracun_cert_lozinka')->nullable()->after('eracun_cert_putanja');
            $table->string('eracun_api_url')->nullable()->after('eracun_cert_lozinka')
                ->comment('Npr. https://demo.efaktura.fina.hr/api/v1');
        });
    }

    public function down(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->dropColumn(['eracun_aktivan', 'eracun_cert_putanja', 'eracun_cert_lozinka', 'eracun_api_url']);
        });
    }
};
