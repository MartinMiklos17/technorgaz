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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Basic fields
            $table->string('item_number');
            $table->unsignedInteger('inventory')->default(0);
            $table->string('name');

            // Price fields
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('consumer_price', 10, 2)->nullable();
            $table->decimal('service_price', 10, 2)->nullable();
            $table->decimal('retail_price', 10, 2)->nullable();
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->decimal('handover_price', 10, 2)->nullable();
            $table->decimal('service_partner_price', 10, 2)->nullable();

            // Text & relational fields
            $table->text('description')->nullable();
            $table->unsignedBigInteger('product_category_id')->nullable();

            // Boolean fields
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_main_carousel')->default(false);
            $table->boolean('show_in_webshop')->default(false);
            $table->boolean('has_electronic_installation_log')->default(false);
            $table->boolean('show_in_spare_parts_list')->default(false);
            $table->boolean('is_main_device')->default(false);

            // Self-referencing relation
            $table->unsignedBigInteger('attached_device_id')->nullable();

            // JSON fields
            $table->json('photos')->nullable();
            $table->json('datasheets')->nullable();

            // Additional fields
            $table->text('notes')->nullable();
            $table->unsignedInteger('low_stock_limit')->default(0);

            // Timestamps
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_category_id')
                ->references('id')
                ->on('product_categories')
                ->onDelete('set null');

            $table->foreign('attached_device_id')
                ->references('id')
                ->on('products')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
