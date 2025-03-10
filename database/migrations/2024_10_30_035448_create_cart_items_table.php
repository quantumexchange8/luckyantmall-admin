<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->string('type')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('trading_master_id');
            $table->integer('quantity');
            $table->decimal('price_per_unit', 13);
            $table->decimal('total_price', 15);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cart_id')
                ->references('id')
                ->on('carts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('trading_master_id')
                ->references('id')
                ->on('trading_masters')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
