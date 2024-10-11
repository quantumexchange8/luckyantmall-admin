<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->longText('descriptions')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->decimal('base_price', 13)->nullable();
            $table->string('discount_type')->nullable();
            $table->string('discount_percentage')->nullable();
            $table->decimal('discount_price', 13)->nullable();
            $table->string('final_price_type')->nullable();
            $table->decimal('final_price', 13)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
