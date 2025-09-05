<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tickets', function (Blueprint $t) {
            $t->ulid('id')->primary(); // ULID per spec
            $t->string('subject');
            $t->text('body'); 
            $t->string('requester_email')->nullable();
            $t->enum('status', ['new','open','closed'])->default('new');

            // AI outputs — store all 3 values
            $t->string('ai_category')->nullable();
            $t->text('ai_explanation')->nullable();
            $t->float('ai_confidence')->nullable();

            // Manual override & job bookkeeping
            $t->string('override_category')->nullable();
            $t->enum('classification_status', ['idle','queued','running','done','failed'])->default('idle');
            $t->timestamp('classified_at')->nullable();

            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tickets'); }
};