---
name: review-prep
description: Chuẩn bị cho buổi review code 20–30 phút sau khi nộp bài: giải thích code đã viết, đoán câu hỏi, luyện sửa nhỏ tại chỗ. Dùng khi người dùng nói "giải thích code", "chuẩn bị review", "người review sẽ hỏi gì", hoặc sau khi xong một task.
---

# Chuẩn bị review

Trong buổi review, người làm phải trình bày bài, giải thích một số đoạn code và sửa nhỏ ngay tại chỗ. Skill này giúp người dùng tự hiểu code, không phải viết sẵn câu trả lời để đọc thuộc.

## Khi người dùng hỏi về một task hoặc một file

1. Đọc code thật trong repo và diff của task (`git diff main...HEAD` hoặc `git log -p`). Không giải thích theo trí nhớ hay theo kế hoạch.
2. Giải thích theo thứ tự một request đi qua: route, middleware, FormRequest, Controller, Service, Model, Resource, Event/Listener. Mỗi bước nói file nào, dòng nào, làm gì.
3. Với mỗi quyết định, nêu cách làm khác và vì sao không chọn. Ví dụ: vì sao attempt ở MongoDB mà không ở MySQL; vì sao chấm trong Service mà không trong Controller.
4. Chỉ ra chỗ yếu thật của code, kể cả khi người dùng không hỏi. Người review sẽ tìm ra.

## Câu hỏi hay gặp cho repo này

| Chủ đề | Câu hỏi |
|---|---|
| Kiến trúc | Vì sao dữ liệu này ở MySQL, dữ liệu kia ở MongoDB? Không có foreign key giữa hai bên thì giữ đúng dữ liệu bằng cách nào? |
| Snapshot | Admin sửa câu hỏi sau khi học sinh nộp bài thì kết quả cũ có đổi không? Vì sao? |
| Chấm điểm | Client sửa request để gửi điểm hoặc đáp án đúng thì sao? Nộp quá giờ xử lý thế nào, giờ lấy từ đâu? |
| Phân quyền | Student gọi thẳng URL admin thì bị chặn ở đâu? Đăng ký kèm `role=admin` thì sao? |
| N+1 | Trang danh sách đề chạy bao nhiêu query? Chứng minh bằng `DB::listen` hoặc Debugbar. |
| Activity log | Vì sao dùng Event/Listener? Listener lỗi thì request chính có lỗi theo không? |
| Index | Index nào phục vụ query nào? Thiếu index thì chuyện gì xảy ra khi dữ liệu lớn? |
| Test | Test chấm điểm kiểm tra gì cụ thể? Vì sao test chạy SQLite, có rủi ro gì? |
| Task 20 | Hai tab cùng bấm "Bắt đầu" thì có tạo 2 attempt không? Chặn ở tầng nào? |

## Luyện sửa tại chỗ

Khi người dùng muốn luyện, đưa ra một yêu cầu sửa nhỏ giống buổi review, ví dụ:

- Thêm điều kiện lọc đề theo thời gian làm bài.
- Đổi cách xử lý nộp quá giờ từ "từ chối" sang "chấm phần đã làm".
- Thêm một trường vào API Resource.
- Thêm một dòng activity log cho hành động mới.

Để người dùng tự làm trước. Chỉ gợi ý file cần mở khi họ hỏi, rồi review lại cách họ sửa và chạy test cùng họ.

## Định dạng trả lời

Ngắn, theo từng bước, có đường dẫn `file:dòng`. Kết thúc bằng 2–3 câu hỏi người review có thể hỏi tiếp về đúng đoạn code đó.
