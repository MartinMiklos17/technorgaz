<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMapColumnsToPartnerDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('partner_details', function (Blueprint $table) {
            // Hozzáadjuk a térkép adatok oszlopait
            $table->string('location_address')->nullable()->after('phone');
            $table->decimal('latitude', 10, 7)->nullable()->after('location_address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down()
    {
        Schema::table('partner_details', function (Blueprint $table) {
            $table->dropColumn(['location_address', 'latitude', 'longitude']);
        });
    }
}
