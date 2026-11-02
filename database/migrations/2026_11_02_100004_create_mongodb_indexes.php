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
     * Dùng table() thay vì create(): migrate:fresh không xóa collection, nên create() sẽ lỗi khi chạy lại.
     */
    public function up(): void
    {
        foreach (['attempts', 'activity_logs'] as $name) {
            Schema::connection('mongodb')->table($name, function (Blueprint $collection) {
                $collection->index('user_id');
                $collection->index('exam_id');
                $collection->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['attempts', 'activity_logs'] as $name) {
            Schema::connection('mongodb')->table($name, function (Blueprint $collection) {
                $collection->dropIndex('user_id_1');
                $collection->dropIndex('exam_id_1');
                $collection->dropIndex('created_at_1');
            });
        }
    }
};
