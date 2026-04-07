<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('racuni', function (Blueprint $table) {
            $table->timestamp('poslan_eracun_at')->nullable()->after('arhiviran_at');
            $table->string('eracun_poruka_id')->nullable()->after('poslan_eracun_at')
                ->comment('ID poruke koji vraća FINA pri slanju');
        });
    }

    public function down(): void
    {
        Schema::table('racuni', function (Blueprint $table) {
            $table->dropColumn(['poslan_eracun_at', 'eracun_poruka_id']);
        });
    }
};
