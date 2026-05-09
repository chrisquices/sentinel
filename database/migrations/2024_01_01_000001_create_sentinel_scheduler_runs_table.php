<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sentinel_scheduler_runs', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->timestamp('ran_at');
            $table->integer('exit_code')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sentinel_scheduler_runs');
    }
};
