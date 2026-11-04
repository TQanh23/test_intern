<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PreviewLog extends Model
{
    // Log chỉ ghi thêm, không bao giờ sửa.
    public const UPDATED_AT = null;

    protected $connection = 'mongodb';

    protected $table = 'preview_logs';

    /**
     * Lưu cả tên gói và tên tài liệu để thống kê không phải join MySQL, xóa gói rồi vẫn còn tên.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'ip',
        'document_id',
        'document_title',
        'package_id',
        'package_name',
    ];
}
