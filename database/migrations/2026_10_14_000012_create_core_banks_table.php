<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::create('core_banks', function (Blueprint $table) {
            $table->id(); $table->string('name', 100)->unique(); $table->timestamps();
        });
        foreach (['Bank BCA','Bank Mandiri','Bank BNI','Bank Permata','Bank CIMB','Bank Jago'] as $name) {
            DB::table('core_banks')->insert(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
    public function down(): void { Schema::dropIfExists('core_banks'); }
};
