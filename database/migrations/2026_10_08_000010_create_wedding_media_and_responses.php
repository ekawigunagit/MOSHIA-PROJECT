<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('wedding_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('core_tenants')->restrictOnDelete();
            $table->string('kind', 10);
            $table->string('path');
            $table->string('mime', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
        Schema::create('wedding_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('wedding_invitations')->cascadeOnDelete();
            $table->uuid('submission_id');
            $table->string('name', 100);
            $table->string('attendance', 20)->nullable();
            $table->unsignedTinyInteger('guests')->default(0);
            $table->text('wish')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamps();
            $table->unique(['invitation_id', 'submission_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('wedding_responses');
        Schema::dropIfExists('wedding_media');
    }
};
