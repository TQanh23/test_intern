---
name: mongo-collections
description: Quy ước cho phần MongoDB của repo (attempts, activity_logs, preview_logs): model, index, aggregation thống kê và cách test. Dùng khi làm task 4, 8, 11–15, 20, 21 hoặc bất cứ code nào chạm connection mongodb.
---

# Phần MongoDB

Package: `mongodb/laravel-mongodb` 5.x. Connection tên `mongodb`, khai báo trong `config/database.php`. Trong Docker app gọi `mongodb://mongo:27017`, từ máy ngoài gọi cổng `27018`.

Trước khi dùng một API của package mà không chắc chữ ký, đọc code trong `vendor/mongodb/laravel-mongodb/src` hoặc tài liệu của bản 5.x. Không đoán theo bản 3.x hoặc 4.x vì namespace đã đổi từ `Jenssegers\Mongodb` sang `MongoDB\Laravel`.

## Model

```php
use MongoDB\Laravel\Eloquent\Model;

class Attempt extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'attempts'; // bản 5.x dùng $table, không còn $collection
}
```

- `user_id`, `exam_id` lưu kiểu int, giống id bên MySQL. Không có foreign key, nên Service phải kiểm tra user và exam tồn tại trước khi ghi.
- Không dùng quan hệ Eloquent chéo MySQL và MongoDB để hiển thị danh sách. Lấy id từ MongoDB rồi `User::whereIn('id', $ids)->get()` một lần, tránh N+1.

## Document attempts (gợi ý cho task 8, 11, 12, 20)

```
{
  user_id: 5, exam_id: 2,
  exam_name: "Đề 1",                   // xóa đề bên MySQL vẫn còn tên
  duration_minutes: 30,
  status: "in_progress" | "submitted",
  started_at, deadline_at,              // deadline_at = started_at + duration, tính phía server
  submitted_at, score, total,
  questions: [                          // snapshot, đã trộn thứ tự (task 11)
    { question_id, content,
      options: [ { option_id, content, is_correct } ],
      selected_option_id, is_correct }
  ],
  created_at, updated_at
}
```

- Snapshot chứa `is_correct` để chấm phía server. API Resource trả cho student khi đang làm bài phải bỏ `is_correct`, nếu không client đọc được đáp án.
- Chấm điểm chỉ dựa vào snapshot và `selected_option_id`, không tin điểm hay cờ đúng/sai client gửi lên.
- Thời gian luôn so với `now()` của server và `deadline_at`. Client chỉ hiển thị.

## Index

Đề yêu cầu index cho `user_id`, `exam_id`, `created_at`. Tạo bằng migration riêng có `protected $connection = 'mongodb';`:

```php
Schema::connection('mongodb')->table('attempts', function (Blueprint $collection) {
    $collection->index('user_id');
    $collection->index('exam_id');
    $collection->index('created_at');
});
```

- Dùng `table()` thay vì `create()`: `create()` báo lỗi khi collection đã có, mà `migrate:fresh` chỉ xóa bảng MySQL, không xóa collection.
- Task 20 (mỗi student chỉ 1 attempt đang mở cho mỗi đề): dùng unique index một phần trên `(user_id, exam_id)` với `partialFilterExpression: { status: "in_progress" }`. Khi hai request tới cùng lúc, database từ chối cái thứ hai, không phụ thuộc vào kiểm tra trong code.
- Kiểm tra index sau khi migrate: `docker compose exec mongo mongosh exam --eval 'db.attempts.getIndexes()'`.

## Aggregation thống kê (task 14, 21)

Dùng pipeline của MongoDB, không load hết document về PHP rồi tính bằng Collection.

```php
Attempt::raw(fn ($collection) => $collection->aggregate([
    ['$match' => ['status' => 'submitted']],
    ['$group' => ['_id' => '$exam_id', 'avg_score' => ['$avg' => '$score'], 'attempts' => ['$sum' => 1]]],
    ['$sort' => ['avg_score' => -1]],
]));
```

- Top 10 học sinh: `$group` theo `user_id`, `$sort`, `$limit: 10`, rồi lấy tên từ MySQL bằng một query `whereIn`.
- Câu sai nhiều nhất: `$unwind: "$questions"`, `$match` câu sai, `$group` theo `questions.question_id`.
- Đặt pipeline trong Service (ví dụ `StatisticsService`), mỗi số liệu một method để dễ test và dễ giải thích.
- Test thống kê: tự tạo vài attempt với điểm biết trước, tính tay kết quả mong đợi, so với kết quả Service.

## Test có MongoDB

`RefreshDatabase` chỉ làm sạch MySQL (SQLite trong test). MongoDB không tự sạch và mặc định vẫn trỏ vào database `exam` dev.

- Thêm `<env name="MONGODB_DATABASE" value="exam_testing"/>` vào `phpunit.xml` để test không ghi vào dữ liệu dev.
- Xóa dữ liệu Mongo trước mỗi test, ví dụ trong `setUp()` của test hoặc một trait dùng chung: `DB::connection('mongodb')->getDatabase()->drop();`. Không dùng `getMongoDB()`, đã deprecated từ 5.2.
- Test Mongo cần container `mongo` đang chạy. Ghi điều này vào README khi thêm test đầu tiên.
- Kiểm tra sau khi chạy test: số document trong `exam.attempts` không đổi.
