<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rate_limit_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('type');
            $table->string('channel');
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->unsignedBigInteger('escalated_to')->nullable();
            $table->timestamps();
            
            $table->index('tenant_id');
            $table->index('type');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_limit_alerts');
    }
};