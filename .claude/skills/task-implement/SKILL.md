---
name: task-implement
description: Làm một task của đề theo kế hoạch đã được duyệt, trên nhánh riêng, rồi chạy test và cập nhật README. Dùng khi người dùng nói "làm task N", "implement", "code theo plan".
---

# Làm task theo kế hoạch đã duyệt

Chỉ bắt đầu khi đã có kế hoạch được người dùng duyệt (từ skill `task-plan` hoặc người dùng dán vào). Nếu chưa có, viết kế hoạch trước và chờ duyệt.

## Bước 1: Nhánh

```bash
git checkout main && git pull
git checkout -b task-<số>-<mô-tả-ngắn>     # ví dụ task-07-mysql-schema
```

Không làm trên `main`. Nếu đang ở nhánh khác có thay đổi chưa commit, hỏi người dùng trước.

## Bước 2: Viết test trước khi viết code

1. Viết Feature test cho tiêu chí "Đạt khi" của task, đặt ở `tests/Feature/<TênTínhNăng>Test.php`.
2. Chạy `docker compose exec app php artisan test --filter=<TênTest>` và xác nhận test fail đúng lý do.
3. Viết code tối thiểu để test pass.

Quy ước test:

- PHPUnit, không dùng Pest. Tên method dạng `test_student_cannot_open_admin_page`.
- Dùng `RefreshDatabase`. `phpunit.xml` phải bật `DB_CONNECTION=sqlite` và `DB_DATABASE=:memory:`; nếu còn comment thì test đang chạy trên MySQL dev và sẽ xóa dữ liệu dev. Dừng lại và báo người dùng.
- Test chạm MongoDB: xem skill `mongo-collections`.
- Mỗi test kiểm tra một hành vi. Kiểm tra cả trường hợp bị từ chối (403, 422, nộp quá giờ), không chỉ trường hợp thành công.

## Bước 3: Code theo quy ước của repo

| Việc | Đặt ở đâu |
|---|---|
| Logic nghiệp vụ (chấm điểm, tạo attempt, thống kê) | `app/Services/<Tên>Service.php`, inject qua constructor |
| Validate | `app/Http/Requests/<Hành động>Request.php` |
| Response API | `app/Http/Resources/<Tên>Resource.php` |
| Phân quyền | middleware cho route admin, Policy nếu cần theo từng bản ghi |
| Activity log | Event + Listener trong `app/Events`, `app/Listeners`, hoặc Observer |
| Model Mongo | `app/Models`, `protected $connection = 'mongodb'` |

Controller chỉ nhận request, gọi Service, trả Resource hoặc view. Khi load quan hệ để hiển thị danh sách, dùng `with()` hoặc `load()`.

Không thêm package, cột hay tính năng ngoài kế hoạch. Nếu phát hiện kế hoạch thiếu hoặc sai, dừng và báo người dùng thay vì tự sửa phạm vi.

## Bước 4: Kiểm tra

Chạy đúng mục "Kiểm tra" của kế hoạch. Tối thiểu:

```bash
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test     # định dạng code
```

Task có migration thì chạy thêm trên MySQL thật:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:rollback --step=<số migration mới>
docker compose exec app php artisan migrate
```

Báo kết quả thật, kể cả lệnh fail. Không tắt hay bỏ qua test để cho xanh.

## Bước 5: README

Cập nhật bảng trạng thái ở mục "Những gì chưa làm được và vì sao": đổi trạng thái task, ghi phần còn thiếu nếu có. Nếu task tạo ra một quyết định kỹ thuật mới (ví dụ chọn Sanctum hay Breeze, chọn từ chối hay chấm phần đã làm khi quá giờ), thêm một dòng vào mục "Quyết định kỹ thuật" kèm lý do.

## Bước 6: Báo lại

Gửi người dùng:

- Danh sách file đã đổi, mỗi file một dòng nói làm gì.
- Kết quả từng lệnh kiểm tra.
- Commit message gợi ý, không có dòng `Co-Authored-By`.
- Những chỗ người review dễ hỏi (xem skill `review-prep`).

Không commit, không push, không mở PR khi người dùng chưa yêu cầu.
