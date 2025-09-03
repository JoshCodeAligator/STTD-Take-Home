<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tickets', function (Blueprint $t) {
            $t->id();
            $t->string('subject');
            $t->text('description');
            $t->string('requester_email');
            $t->enum('status', ['new','open','closed'])->default('new');
            $t->string('ai_category')->nullable();
            $t->float('ai_confidence')->nullable();
            $t->string('override_category')->nullable();
            $t->enum('classification_status', ['idle','queued','running','done','failed'])->default('idle');
            $t->timestamp('classified_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tickets');
    }
};
