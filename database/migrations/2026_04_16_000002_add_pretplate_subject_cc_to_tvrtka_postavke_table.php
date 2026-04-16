<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->string('pretplate_email_subject')->nullable()->after('pretplate_email_predlozak');
            $table->string('pretplate_email_cc')->nullable()->after('pretplate_email_subject');
        });
    }

    public function down(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->dropColumn(['pretplate_email_subject', 'pretplate_email_cc']);
        });
    }
};
