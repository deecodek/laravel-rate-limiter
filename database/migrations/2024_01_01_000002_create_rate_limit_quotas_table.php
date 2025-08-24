<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rate_limit_quotas', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('period_key');
            $table->integer('used')->default(0);
            $table->integer('limit')->default(0);
            $table->integer('rollover_available')->default(0);
            $table->timestamp('resets_at')->nullable();
            
            $table->primary(['tenant_id', 'subject_type', 'subject_id', 'period_key']);
            $table->index(['tenant_id', 'subject_type', 'subject_id']);
            $table->index('resets_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_limit_quotas');
    }
};