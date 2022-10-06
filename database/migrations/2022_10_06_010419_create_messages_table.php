<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', static function (Blueprint $table) {
            $table->id();
            $table->string('message_id');
            $table->string('conversation_id');
            $table->string('platform')->nullable();
            $table->string('to', 25);
            $table->string('from', 25);
            $table->string('type', 25);
            $table->longText('content')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
