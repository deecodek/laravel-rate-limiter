<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rate_limit_attempts', function (Blueprint $table) {
            $table->id();
            $table->timestamp('ts')->useCurrent();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip')->index();
            $table->string('token')->nullable();
            $table->string('route')->index();
            $table->string('method')->index();
            $table->integer('weight')->default(1);
            $table->integer('cost')->default(1);
            $table->string('decision');
            $table->string('reason')->nullable();
            
            $table->index(['tenant_id', 'ts']);
            $table->index(['tenant_id', 'user_id', 'ts']);
            $table->index(['tenant_id', 'ip', 'ts']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_limit_attempts');
    }
};