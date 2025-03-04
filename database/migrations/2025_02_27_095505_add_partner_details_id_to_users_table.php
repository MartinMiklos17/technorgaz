<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerDetailsIdToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Hozzáadjuk a partner_details_id oszlopot, amely idegen kulcs a partner_details táblára
            $table->foreignId('partner_details_id')
                ->nullable()
                ->constrained('partner_details')
                ->onDelete('set null')
                ->after('id'); // Az 'id' oszlop után, módosítható igény szerint
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Eltávolítjuk a foreign key-t és az oszlopot
            $table->dropConstrainedForeignId('partner_details_id');
        });
    }
}
