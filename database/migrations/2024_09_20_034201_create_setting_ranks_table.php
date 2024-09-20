<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('setting_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('position')->nullable();
            $table->string('lot_rebate_currency')->nullable();
            $table->decimal('lot_rebate_amount')->nullable();
            $table->integer('min_direct_referral')->nullable();
            $table->unsignedBigInteger('min_direct_referral_rank_id')->nullable();
            $table->string('group_sales_currency')->nullable();
            $table->decimal('max_capped_per_line', 13)->nullable();
            $table->decimal('min_group_sales', 13)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('min_direct_referral_rank_id')
                ->references('id')
                ->on('setting_ranks')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_ranks');
    }
};
