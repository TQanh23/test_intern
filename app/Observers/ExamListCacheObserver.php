<?php

namespace App\Observers;

use App\Services\ExamService;

/**
 * Gắn vào Exam và Question: đổi tên đề, thêm hay xóa câu hỏi đều làm danh sách đề cho student thay đổi.
 */
class ExamListCacheObserver
{
    public function saved(): void
    {
        ExamService::forgetAvailableCache();
    }

    public function deleted(): void
    {
        ExamService::forgetAvailableCache();
    }
}
