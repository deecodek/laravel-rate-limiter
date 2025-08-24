<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rate_limit_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('ip')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('token')->nullable();
            $table->string('reason')->nullable();
            $table->integer('level')->default(1);
            $table->boolean('permanent')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('tenant_id');
            $table->index('ip');
            $table->index('user_id');
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_limit_blocks');
    }
};