<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('partner_details', function (Blueprint $table) {
            $table->id();
            // Ha a company_details a companies táblához kapcsolódik, akkor:
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->boolean('client_take')->default(false);
            $table->boolean('complete_execution')->default(false);
            $table->string('gas_installer_license')->nullable();
            $table->date('license_expiration')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_details');
    }
}
