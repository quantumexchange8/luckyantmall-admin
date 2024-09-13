<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_masters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('meta_login')->nullable();
            $table->string('asset_name')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->unsignedMediumInteger('total_investors')->nullable();
            $table->decimal('minimum_investment')->nullable();
            $table->string('minimum_investment_period_type')->nullable();
            $table->integer('minimum_investment_period')->nullable();
            $table->string('status')->nullable()->default('active');
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('edited_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_masters');
    }
};
