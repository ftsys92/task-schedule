<?php

use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('notes', 1000)->nullable();
            $table->string('duration')->nullable();
            $table->string('status');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->foreignIdFor(User::class, 'assignee_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
