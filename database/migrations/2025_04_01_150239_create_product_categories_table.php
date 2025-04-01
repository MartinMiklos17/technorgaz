<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->text('short_description')->nullable();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();

            // Ha van users és companies tábla, és külső kulcsot akarsz használni:
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
}
