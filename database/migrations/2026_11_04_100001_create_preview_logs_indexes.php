<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * Dùng table() thay vì create(), cùng lý do với migration index của attempts.
     */
    public function up(): void
    {
        Schema::connection('mongodb')->table('preview_logs', function (Blueprint $collection) {
            $collection->index('document_id');
            $collection->index('package_id');
            $collection->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->table('preview_logs', function (Blueprint $collection) {
            $collection->dropIndex('document_id_1');
            $collection->dropIndex('package_id_1');
            $collection->dropIndex('created_at_1');
        });
    }
};
