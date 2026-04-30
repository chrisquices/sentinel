<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vulcan_sentinel_scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->string('expression', 50);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->longText('output')->nullable();
            $table->text('exception')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vulcan_sentinel_scheduled_tasks');
    }
};
