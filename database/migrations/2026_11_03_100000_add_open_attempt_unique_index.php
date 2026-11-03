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
     * Mỗi student chỉ có 1 bài đang làm cho mỗi đề. Database từ chối bài thứ hai kể cả khi hai request tới cùng lúc.
     * Index chỉ áp dụng cho bài in_progress, nên bài đã nộp không bị giới hạn.
     */
    public function up(): void
    {
        Schema::connection('mongodb')->table('attempts', function (Blueprint $collection) {
            $collection->unique(['user_id', 'exam_id'], 'one_open_attempt_per_exam', null, [
                'partialFilterExpression' => ['status' => 'in_progress'],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->table('attempts', function (Blueprint $collection) {
            $collection->dropIndex('one_open_attempt_per_exam');
        });
    }
};
