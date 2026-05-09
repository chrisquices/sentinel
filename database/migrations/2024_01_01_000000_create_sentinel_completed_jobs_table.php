<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sentinel_completed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable()->index();
            $table->string('connection');
            $table->string('queue');
            $table->string('job_class');
            $table->string('display_name');
            $table->unsignedInteger('attempts')->default(1);
            $table->unsignedInteger('run_time')->nullable()->comment('milliseconds');
            $table->timestamp('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sentinel_completed_jobs');
    }
};