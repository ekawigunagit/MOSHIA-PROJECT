<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        // Preserve IDs referenced by existing draft/published JSON snapshots.
        if (! Schema::hasTable('core_media')) { Schema::rename('wedding_media', 'core_media'); }
        if (! Schema::hasColumn('core_media', 'collection')) {
            Schema::table('core_media', fn (Blueprint $table) => $table->string('collection', 50)->default('wedding')->index());
        }
    }
    public function down(): void {
        if (\Illuminate\Support\Facades\DB::table('core_media')->where('collection', '!=', 'wedding')->exists()) {
            throw new \RuntimeException('Cannot roll back shared media while other product collections exist.');
        }
        if (Schema::hasColumn('core_media', 'collection')) {
            Schema::table('core_media', function (Blueprint $table) {
                $table->dropIndex('core_media_collection_index');
                $table->dropColumn('collection');
            });
        }
        Schema::rename('core_media', 'wedding_media');
    }
};
