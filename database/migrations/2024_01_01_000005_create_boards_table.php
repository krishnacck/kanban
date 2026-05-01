<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Add board_id to countries, statuses, tasks
        Schema::table('countries', function (Blueprint $table) {
            $table->foreignId('board_id')->nullable()->constrained('boards')->cascadeOnDelete()->after('id');
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->foreignId('board_id')->nullable()->constrained('boards')->cascadeOnDelete()->after('id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('board_id')->nullable()->constrained('boards')->cascadeOnDelete()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', fn($t) => $t->dropForeign(['board_id']));
        Schema::table('statuses', fn($t) => $t->dropForeign(['board_id']));
        Schema::table('countries', fn($t) => $t->dropForeign(['board_id']));
        Schema::dropIfExists('boards');
    }
};
