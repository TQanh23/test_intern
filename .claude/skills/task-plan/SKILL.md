---
name: task-plan
description: Viết kế hoạch cho một task của đề (task 1–22) trước khi code. Dùng khi người dùng nói "lên plan task N", "plan task N", hoặc bắt đầu một task mới.
---

# Lên kế hoạch cho một task

Mục tiêu: một kế hoạch ngắn, đủ để người dùng duyệt và sau đó tự giải thích được trong buổi review. Không viết code ở bước này, chỉ đọc repo.

## Bước 1: Đọc bối cảnh

1. Đọc yêu cầu và tiêu chí hoàn thành của task trong bảng dưới. Nếu người dùng dán yêu cầu khác, theo bản người dùng dán.
2. Đọc bảng trạng thái trong `README.md` để biết task nào đã xong, task nào làm dở.
3. Đọc code hiện có liên quan: migration, model, route, test. Ghi lại những gì đã có để không làm trùng.
4. Kiểm tra các task phụ thuộc. Ví dụ task 12 cần task 8 và 11; task 14 cần dữ liệu `attempts` có điểm.

## Bước 2: Viết kế hoạch theo đúng các mục sau

```
Task N: <tên>

Context
<Đề yêu cầu gì, tiêu chí là gì. Hiện repo đã có gì, còn thiếu gì.>

Thiết kế
<Schema, route, class, luồng dữ liệu. Dùng bảng khi liệt kê cột hoặc endpoint.>

Quyết định chính
<Mỗi quyết định một dòng, kèm lý do và hệ quả với task khác.>

Thay đổi
<Từng file tạo mới hoặc sửa, đường dẫn đầy đủ, nội dung chính.>

Test
<Tên file test và từng trường hợp sẽ kiểm tra.>

Không làm trong task này
<Việc gần giống nhưng thuộc task khác, ghi rõ số task.>

Kiểm tra
<Lệnh chạy được ngay, dạng docker compose exec app ...>
Đạt khi: <điều kiện cụ thể, đo được>

Commit gợi ý: <type>: <mô tả ngắn bằng tiếng Anh>

Rủi ro
<Điều kế hoạch có thể bỏ sót và cách bù.>
```

## Nguyên tắc

- Chỉ làm đúng phạm vi task. Thứ đề không yêu cầu thì đưa vào "Không làm trong task này".
- Mỗi quyết định phải trả lời được câu "vì sao không làm cách khác?" vì người review sẽ hỏi.
- Ghi rõ tác động lên task sau. Ví dụ: xóa đề cascade sang câu hỏi thì snapshot trong `attempts` (task 8) phải chứa cả tên đề.
- Ưu tiên tính năng có sẵn của Laravel (FormRequest, API Resource, Policy, Event) hơn code tự viết.
- Kế hoạch dài tối đa khoảng một màn hình. Bỏ mục nào không áp dụng.
- Dừng lại sau khi gửi kế hoạch, chờ người dùng duyệt. Sau khi duyệt, dùng skill `task-implement`.

## Yêu cầu của đề

| # | Task | Yêu cầu | Đạt khi |
|---|---|---|---|
| 1 | Khởi tạo Laravel 11 | PHP >= 8.2, commit theo từng bước | Lịch sử commit rõ ràng |
| 2 | Docker | php-fpm, nginx, MySQL 8, MongoDB 7 | `docker compose up` chạy được |
| 3 | Kết nối MySQL | .env, migration, seeder admin, student, đề mẫu | `php artisan migrate --seed` không lỗi |
| 4 | Kết nối MongoDB | `mongodb/laravel-mongodb`, connection `mongodb` | Model Mongo đọc/ghi được |
| 5 | Command | `app:check-connections` in trạng thái MySQL và MongoDB | Thấy OK cho cả 2 DB |
| 6 | README | Chạy từ đầu trong khoảng 5 lệnh; giải thích quyết định; mục "Những gì chưa làm được và vì sao" | Người khác clone về chạy được, trung thực về phần thiếu |
| 7 | Schema MySQL | users, exams (tên, thời gian), questions, options (cờ đúng/sai); FK và index | Migration hợp lý, quan hệ đúng |
| 8 | Collection MongoDB | attempts (snapshot câu hỏi, câu trả lời, điểm, giờ bắt đầu/nộp), activity_logs | Index cho user_id, exam_id, created_at |
| 9 | Auth + phân quyền | Sanctum hoặc Breeze, role admin/student, middleware | Student không vào được trang admin |
| 10 | CRUD đề và câu hỏi | FormRequest, phân trang, tìm theo tên | Tạo/sửa/xóa/tìm đúng |
| 11 | Bắt đầu làm bài | Tạo attempt trong MongoDB, trộn thứ tự câu | Mỗi lần làm có attempt riêng, thứ tự khác nhau |
| 12 | Nộp bài, chấm điểm | Chấm phía server trong Service; quá giờ thì từ chối hoặc chấm phần đã làm | Điểm đúng, client không gian lận được |
| 13 | Trang kết quả | Điểm, câu sai, đáp án đúng | Đúng dữ liệu của attempt |
| 14 | Thống kê admin | Aggregation: điểm TB theo đề, top 10 học sinh, câu sai nhiều nhất | Số liệu khớp dữ liệu thật |
| 15 | Activity log | Event/Listener hoặc Observer | Không ghi log rải rác trong controller |
| 16 | API REST | `GET /api/exams`, `POST /api/attempts/{id}/submit`, API Resource | Đúng format và status code |
| 17 | Tổ chức code | Service, FormRequest, eager loading | Controller mỏng, không N+1 |
| 18 | Feature test | Ít nhất 5, bắt buộc test chấm điểm và nộp quá giờ | `php artisan test` pass |
| 19 | Bonus | Cache danh sách đề, Swagger hoặc Postman | Làm mục nào tính mục đó |
| 20 | Autosave (nâng cao) | Lưu đáp án từng câu; reload tiếp tục attempt cũ, thời gian theo server; mỗi student 1 attempt mở mỗi đề | Không mất đáp án, không 2 attempt song song, đồng hồ không reset |
| 21 | Gói IELTS BE (nâng cao) | packages, units, documents; seeder Mindset level 1–3 với file mẫu tự tạo; API danh sách, chi tiết, preview signed URL; file gốc private; preview_logs trong MongoDB; rate limit | Không truy cập file gốc; URL hết hạn trả 403; đúng số trang giới hạn; có Feature test preview |
| 22 | Gói IELTS FE (nâng cao) | Trang card, chi tiết accordion, modal pdf.js và audio, thống kê admin | Đẹp trên desktop và mobile, không reload, có loading/lỗi/rỗng |

Phải làm ít nhất 1 trong 3 task 20–22.
