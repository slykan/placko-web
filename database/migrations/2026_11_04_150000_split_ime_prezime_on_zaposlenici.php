<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zaposlenici', function (Blueprint $table) {
            $table->string('ime')->nullable()->after('tvrtka_id');
            $table->string('prezime')->nullable()->after('ime');
        });

        // Postojeće "Ime Prezime": prva riječ je ime, ostatak prezime.
        foreach (DB::table('zaposlenici')->get(['id', 'ime_prezime']) as $z) {
            $dijelovi = preg_split('/\s+/', trim((string) $z->ime_prezime), 2);
            DB::table('zaposlenici')->where('id', $z->id)->update([
                'ime' => $dijelovi[0] ?? '',
                'prezime' => $dijelovi[1] ?? '',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('zaposlenici', function (Blueprint $table) {
            $table->dropColumn(['ime', 'prezime']);
        });
    }
};
