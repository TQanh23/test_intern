# Thiết kế lại giao diện (UI redesign)

Ngày: 2026-11-09. Trạng thái: đã duyệt hướng trong buổi trao đổi, chờ duyệt tài liệu.

## Mục đích và lý do

Giao diện hiện tại vẫn là giao diện mặc định của Laravel Breeze: xám, phẳng, nhìn như bản nháp. Người làm muốn toàn bộ màn hình trông "hiện đại" trước buổi review. Hướng đã chọn (phong cách B trong mockup) là **"học tập tươi sáng"**: thân thiện với học sinh, nhiều màu nhẹ, bo tròn lớn.

Thành công nghĩa là: mở bất kỳ màn hình nào cũng thấy cùng một phong cách, mọi chức năng chạy y như cũ, và người làm vẫn giải thích được từng class trong buổi review.

## Mục tiêu

- Đổi giao diện **tất cả** màn hình Blade trong `resources/views` (khoảng 45 file), kể cả trang `/` (`welcome.blade.php`, hiện là trang mặc định của Laravel).
- Hai khung trang:
  - **Học sinh và trang dùng chung** (dashboard, làm bài, kết quả, gói IELTS, hồ sơ): menu trên cùng.
  - **Admin** (dashboard quản trị, đề thi, câu hỏi, thống kê, thống kê xem thử): sidebar dọc màu tối.
- Trang đăng nhập, đăng ký, quên mật khẩu, xác minh email: khung riêng (guest layout) theo cùng phong cách.

## Không làm (non-goals)

- Không đổi hành vi: route, controller, service, validate, autosave, đồng hồ đếm ngược, phân quyền giữ nguyên. Không sửa file PHP nào ngoài `app/View/Components` (nếu cần thêm layout admin).
- Không thêm dữ liệu mới lên màn hình. Banner chào chỉ có lời chào và tên, **không** có số "đề đang chờ" (quyết định của Claude, đã nói với người làm).
- Không làm chế độ tối (dark mode), không thêm animation ngoài `transition` có sẵn của Tailwind.
- Không đổi trang làm bài sang một câu mỗi trang. Vẫn hiện toàn bộ câu hỏi trên một trang như hiện tại.

## Thất bại dù vẫn chạy được (anti-goals)

- Thêm thư viện UI (DaisyUI, Flowbite, icon pack…) hoặc CSS viết tay dài. Lúc review người làm phải giải thích được mọi dòng.
- Làm vỡ test: 172 test hiện có phải qua mà **không sửa test**.
- Mất khả năng truy cập (accessibility) đang có: `sr-only`, `aria-live`, `aria-label`, `<legend>`, `scope="col"`, focus ring.
- Giao diện vỡ trên điện thoại.

## Ràng buộc

- Stack giữ nguyên: Tailwind 3 + `@tailwindcss/forms`, Alpine 3, Vite. Container `app` không có Node; asset được build bởi service `assets` (`node:22-alpine`, chạy `npm ci && npm run build` rồi thoát). Build lại bằng `docker compose run --rm assets`. Tailwind đang là 3.4.19 nên dùng được biến thể `has-[:checked]:`.
- Làm trên nhánh mới từ `main`. Không commit khi chưa được yêu cầu (CLAUDE.md).
- Comment trong code bằng tiếng Việt.
- **Test kiểm tra chuỗi HTML.** Những chuỗi sau phải còn nguyên trong output (giữ nguyên chữ và thứ tự thuộc tính):
  - `value="{id}" checked` trên radio đáp án (`AutosaveTest`): giữ `value` ngay trước `@checked`.
  - `left: 1200,` trong `x-data` của trang làm bài (`AutosaveTest`).
  - `x-data="packageList"`, `packageDetail('slug')`, `x-data="previewStats"` (`PackagePagesTest`).
  - Các chữ: "Câu đúng", "Câu sai", "Chưa trả lời", "Bạn chọn", "Đáp án đúng", "Đã hết giờ", "Chấm bài", "Câu 1", điểm dạng `3/3`, phần trăm trên trang thống kê theo đúng thứ tự hiện tại.
  - Trang `/dashboard` của admin phải còn link tới `route('admin.dashboard')` (`AdminAccessTest`).
  - Link phân trang giữ `search=` và `page=` (`ExamManagementTest`).
  - **Chuỗi không được xuất hiện:**
    - Form tạo câu hỏi không được chứa chuỗi `checked` ở bất kỳ đâu (`QuestionManagementTest` gọi `assertDontSee('checked', false)`). Vì vậy layout admin, sidebar và `admin/questions/_form` không được dùng `has-[:checked]:`, `peer-checked:` hay Alpine đọc `checked`.
    - Trang làm bài không được lộ `is_correct` hay "Đáp án đúng".
    - Trang hết giờ không được có `type="radio"` hay "Nộp bài".
    - Trang `/dashboard` của học sinh không được có link admin.
  - Cách chắc nhất: chỉ đổi class và thẻ bọc ngoài, không đổi chữ hiển thị đang có. Chạy `docker compose exec app php artisan test` sau mỗi nhóm màn hình.

## Phong cách (đã chốt)

| Thành phần | Quy ước |
| --- | --- |
| Nền trang | `bg-slate-50` |
| Thẻ (card) | `bg-white rounded-2xl shadow-sm`, padding `p-6` |
| Màu nhấn | gradient `from-sky-500 to-indigo-500` cho banner và mục đang chọn; chữ logo `text-sky-500 font-extrabold` |
| Nút chính | `bg-slate-900 text-white rounded-full`, không viết hoa toàn bộ (bỏ `uppercase tracking-widest` của Breeze) |
| Nút phụ / nút nguy hiểm | cùng dáng viên thuốc `rounded-full`; phụ là `bg-white ring-1 ring-slate-200`, nguy hiểm là `bg-red-600` |
| Ô nhập | `rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500` |
| Nhãn nhỏ (badge) | `rounded-full px-2.5 py-0.5 text-xs font-semibold`, nền pastel (`bg-sky-100 text-sky-700`, `bg-violet-100 text-violet-700`…) |
| Chữ tiêu đề | `font-extrabold text-slate-900`, tiêu đề trang cỡ `text-2xl` |
| Font | giữ Figtree, thêm weight 700 và 800 vào link Bunny Fonts |

Màu và dáng nút, ô nhập đặt **trong các component Blade có sẵn** (`primary-button`, `secondary-button`, `danger-button`, `text-input`, `input-label`, `nav-link`, `responsive-nav-link`, `dropdown`, `modal`, `auth-session-status`, `input-error`, `async-state`, `ielts/*`, `vendor/pagination/tailwind`), để mỗi kiểu chỉ sửa ở một chỗ. Màn hình nào đang viết class trực tiếp (ví dụ link "Sửa" màu indigo, nút "Xóa" màu đỏ) thì đổi sang cùng quy ước.

## Khung trang

### Học sinh và trang chung (`layouts/app.blade.php`, `layouts/navigation.blade.php`)

- Thanh menu trên cùng nền trắng, viền dưới `border-slate-100`. Bên trái là logo chữ "ExamPro" (thay cho logo SVG Laravel), bên phải là các link và dropdown tên người dùng.
- Link đang chọn: chữ đậm `text-slate-900` và gạch chân hoặc nền viên thuốc nhạt. Link khác: `text-slate-500`.
- Trên điện thoại vẫn dùng nút ☰ của Breeze (Alpine `open`).
- Slot `header` không còn là dải trắng có bóng. Tiêu đề trang nằm trong vùng nội dung, chữ to.
- Dashboard (`/dashboard`, mọi vai trò đều vào đây sau khi đăng nhập): banner gradient "Chào {tên} 👋" và các lối tắt dạng thẻ pastel. Học sinh thấy "Làm bài" và "Gói IELTS". Admin thấy "Quản trị" (link `admin.dashboard`) và "Gói IELTS", vì `exams.index` chặn admin (403). Chọn lối tắt bằng `isAdmin()`/`isStudent()` như `navigation.blade.php` đang làm.

### Admin (khung mới)

- Sidebar trái rộng khoảng `w-64`, nền `bg-slate-900`, chữ `text-slate-300`. Logo ở trên cùng. Mục đang mở tô gradient sky→indigo, chữ trắng.
- Các mục: Tổng quan (`admin.dashboard`), Đề thi (`admin.exams.*`, gồm cả trang câu hỏi), Thống kê (`admin.statistics`), Lượt xem thử (`admin.preview-statistics`), Gói IELTS (`packages.index`). Phía dưới: tên người dùng, Hồ sơ, Đăng xuất.
- Trên điện thoại sidebar ẩn đi, có thanh trên cùng với nút ☰ mở sidebar dạng ngăn kéo (Alpine, cùng cách nút ☰ của Breeze).
- Nội dung bên phải nền `bg-slate-50`, dùng chung kiểu thẻ.
- Các view trong `resources/views/admin/**` đổi `<x-app-layout>` thành layout admin.
- Dashboard admin: thẻ lối tắt pastel thay cho ba link chữ.

Trang gói IELTS và trang hồ sơ dùng khung học sinh cho mọi vai trò (quyết định của Claude: đây là trang chung, giữ một view). Admin vào từ sidebar sẽ sang khung có menu trên cùng; menu trên cùng đã có link "Quản trị" để quay lại.

### Khách (`layouts/guest.blade.php`)

- Nền gradient sky→indigo phủ toàn màn hình, form nằm trong thẻ trắng `rounded-2xl` ở giữa, logo chữ phía trên.

## Từng nhóm màn hình

- **Làm bài (`student/exams/index`)**: lưới thẻ 2 cột (1 cột trên điện thoại), mỗi thẻ có badge thời gian, tên đề, số câu, nút "Bắt đầu →". Nền thẻ xoay vòng vài màu pastel.
- **Đang làm bài (`student/attempts/show`)**:
  - Thanh dính trên cùng thành banner gradient: tên đề, hạn nộp, trạng thái lưu (`aria-live` giữ nguyên), đồng hồ trong viên thuốc trắng mờ.
  - Thanh tiến độ: số câu đã chọn / tổng số câu, tính bằng Alpine từ các radio đang được chọn (chỉ hiển thị, không gửi đi đâu).
  - Mỗi câu là một thẻ với badge "Câu n / tổng". Đáp án là ô bo tròn viền `border-2`. Ô đang chọn viền indigo, nền `bg-indigo-50` (dùng `has-[:checked]:` của Tailwind 3.4, hoặc `peer`). Radio vẫn là `<input type="radio">` thật để autosave và test không đổi.
  - Hết giờ: thẻ thông báo nền vàng nhạt, nút "Chấm bài".
- **Kết quả**: banner gradient hiện điểm to. Mỗi câu một thẻ: đúng viền xanh lá, sai viền đỏ, chưa trả lời viền xám. Giữ nguyên các chữ "(Bạn chọn)", "(Đáp án đúng)".
- **Admin đề thi và câu hỏi** (`admin/exams/*`, `admin/questions/*`): nút "Tạo đề thi" thành nút chính. Ô tìm kiếm bo tròn. Bảng nằm trong thẻ bo tròn, hàng có hover `bg-slate-50`. "Sửa" và "Xóa" thành nút nhỏ viên thuốc. Form đặt trong thẻ. Badge "đáp án đúng" chỉ đặt ở danh sách câu hỏi tĩnh trên trang sửa đề (`admin/exams/edit`), thay cho chữ "(đáp án đúng)" và giữ nguyên chữ đó trong badge. Form câu hỏi (`admin/questions/_form`) chỉ đổi kiểu ô, không có hiệu ứng theo radio đang chọn (xem ràng buộc về chuỗi `checked`).
- **Thống kê** (`admin/statistics`, `admin/preview-statistics`): không thêm con số tổng nào (không có dữ liệu mới). Mỗi phần đang có (ba bảng ở `statistics`, hai danh sách top 10 ở `preview-statistics`) nằm trong một thẻ riêng, tiêu đề phần có badge pastel, mỗi phần một màu. Bảng ở `statistics` theo kiểu bảng đề thi, thứ tự chữ giữ nguyên (test kiểm tra thứ tự). Thanh ngang của top 10 đổi sang màu gradient sky→indigo, bo tròn.
- **Gói IELTS** (`packages/*`, `components/ielts/*`, `resources/js/ielts/format.js`): thẻ gói bo tròn, badge level dùng lại bảng `LEVEL_CLASSES` (có thể đổi tông màu nhưng giữ ba level khác màu nhau). Modal xem thử `rounded-2xl`.
- **Hồ sơ** (`profile/*`): ba form trong ba thẻ, form xóa tài khoản dùng nút nguy hiểm.
- **Trang `/` (`welcome`)**: thay trang mặc định 176 dòng của Laravel bằng trang giới thiệu ngắn: logo, một câu mô tả, nút "Đăng nhập" và "Đăng ký" (hoặc "Vào dashboard" nếu đã đăng nhập), nền gradient như guest layout.
- **Phân trang (`vendor/pagination/tailwind`)**: số trang dạng viên tròn, trang hiện tại nền `bg-slate-900`.

## Để người làm tự quyết

- Layout admin làm bằng class component (`app/View/Components/AdminLayout.php` + `layouts/admin.blade.php`, giống `AppLayout`) hay anonymous component. Ưu tiên cách giống `AppLayout` để nhất quán.
- Tên và bố cục chính xác của các component mới nếu cần (ví dụ `x-card`). Chỉ thêm khi dùng lại từ 3 chỗ trở lên.
- Màu pastel cụ thể cho từng thẻ, cỡ chữ, khoảng cách, miễn là theo bảng quy ước ở trên.
- Thứ tự làm các nhóm màn hình.

## Kiểm tra khi xong

- `docker compose exec app php artisan test`: toàn bộ test qua, không sửa test.
- `docker compose exec app ./vendor/bin/pint --test` (nếu có đụng file PHP).
- Build asset thành công.
- Mở bằng trình duyệt ở cỡ desktop và cỡ điện thoại (khoảng 375px): đăng nhập admin, xem đề, sửa câu hỏi, xem thống kê; đăng nhập học sinh, làm bài (autosave hiện "Đã lưu", đồng hồ chạy), nộp bài, xem kết quả, mở gói IELTS và modal xem thử.
- Cập nhật `README.md` một dòng về giao diện mới nếu README có mô tả giao diện.
