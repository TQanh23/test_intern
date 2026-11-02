# Hệ thống thi trắc nghiệm (bài test intern)

Laravel 11 (PHP 8.3), MySQL 8, MongoDB 7 (`mongodb/laravel-mongodb` 5.x), chạy bằng Docker. Đề bài gồm 22 task, nằm trong file `Pros Test.xlsx` (không có trong repo). Bảng trạng thái từng task ở mục "Những gì chưa làm được và vì sao" của `README.md`.

Sau khi nộp có buổi review 20–30 phút: người làm phải giải thích được mọi dòng code. Viết code đơn giản, dễ giải thích hơn là code khéo.

## Quy ước

- Luôn tạo nhánh mới từ `main` cho mỗi task.
- Không commit, không push khi chưa được yêu cầu. Commit message theo Conventional Commits (`feat:`, `fix:`, `chore:`, `docs:`, `test:`), không có dòng `Co-Authored-By`.
- Mọi lệnh PHP chạy trong container: `docker compose exec app php artisan ...`, `docker compose exec app composer ...`.
- Thêm migration mới, không sửa migration đã có, để database đang chạy chỉ cần `migrate`.
- MySQL giữ `users`, `exams`, `questions`, `options`. MongoDB giữ `attempts` (có snapshot đề) và `activity_logs`. Lý do ở `README.md`, mục "Quyết định kỹ thuật".
- Controller mỏng: logic ở `app/Services`, validate ở FormRequest, response API qua API Resource, activity log qua Event/Listener hoặc Observer (không ghi log trong controller), eager loading để tránh N+1.
- Không đưa `role` vào `$fillable` của `User`.
- Test dùng PHPUnit (không dùng Pest), Feature test dùng `RefreshDatabase`, chạy trên SQLite `:memory:` để không xóa dữ liệu dev.
- Viết README, comment và tài liệu bằng tiếng Việt. Tên class, biến, cột bằng tiếng Anh.

## Skill trong repo

- `task-plan`: viết kế hoạch cho một task trước khi code.
- `task-implement`: làm task theo kế hoạch đã duyệt, chạy kiểm tra, cập nhật README.
- `mongo-collections`: model, index, aggregation và test cho phần MongoDB.
- `review-prep`: chuẩn bị giải thích code cho buổi review.
