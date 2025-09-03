<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->string('author')->default('agent');
            $t->text('body');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('notes');
    }
};
