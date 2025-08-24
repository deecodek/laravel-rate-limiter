<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rate_limit_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->json('dimensions');
            $table->string('algorithm')->default('token_bucket');
            $table->json('limits');
            $table->integer('window')->default(60);
            $table->integer('weight')->default(1);
            $table->integer('burst')->default(0);
            $table->integer('cooldown')->default(0);
            $table->boolean('enabled')->default(true);
            $table->integer('priority')->default(0);
            $table->unsignedBigInteger('inherited_from_id')->nullable();
            $table->timestamps();
            
            $table->index('tenant_id');
            $table->index('priority');
            $table->index('enabled');
            $table->foreign('inherited_from_id')->references('id')->on('rate_limit_rules')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_limit_rules');
    }
};