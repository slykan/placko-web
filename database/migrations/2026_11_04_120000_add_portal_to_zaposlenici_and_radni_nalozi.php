<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zaposlenici', function (Blueprint $table) {
            $table->string('portal_token', 64)->nullable()->unique()->after('napomena');
        });

        Schema::table('radni_nalog_zaposlenik', function (Blueprint $table) {
            $table->timestamp('prihvacen_at')->nullable();
        });

        Schema::table('radni_nalozi', function (Blueprint $table) {
            $table->longText('potpis_klijenta')->nullable()->after('napomena');
            $table->string('potpisao')->nullable()->after('potpis_klijenta');
            $table->timestamp('potpisano_at')->nullable()->after('potpisao');
        });
    }

    public function down(): void
    {
        Schema::table('radni_nalozi', function (Blueprint $table) {
            $table->dropColumn(['potpis_klijenta', 'potpisao', 'potpisano_at']);
        });

        Schema::table('radni_nalog_zaposlenik', function (Blueprint $table) {
            $table->dropColumn('prihvacen_at');
        });

        Schema::table('zaposlenici', function (Blueprint $table) {
            $table->dropUnique(['portal_token']);
            $table->dropColumn('portal_token');
        });
    }
};
