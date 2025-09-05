<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notes', function (Blueprint $t) {
            $t->id();
            $t->foreignUlid('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->string('author')->default('agent');
            $t->text('body');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notes'); }
};