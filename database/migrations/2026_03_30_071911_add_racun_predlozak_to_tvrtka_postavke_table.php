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
            $table->text('racun_email_predlozak')->nullable()->after('pretplate_email_predlozak');
        });
    }

    public function down(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->dropColumn('racun_email_predlozak');
        });
    }
};
