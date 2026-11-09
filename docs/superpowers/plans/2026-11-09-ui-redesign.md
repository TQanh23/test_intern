# Kế hoạch triển khai: thiết kế lại giao diện

> **Cho agent thực thi:** BẮT BUỘC dùng skill phụ superpowers:subagent-driven-development (khuyên dùng) hoặc superpowers:executing-plans để làm từng task. Các bước dùng checkbox (`- [ ]`) để theo dõi.

**Mục tiêu:** Đổi toàn bộ màn hình Blade sang phong cách "học tập tươi sáng". Admin dùng sidebar tối riêng. Không đổi hành vi.

**Kiến trúc:** Kiểu nút và ô nhập đặt trong các component Blade có sẵn. Có ba khung trang: `layouts/app` (menu trên cùng), `layouts/admin` (sidebar tối, layout mới) và `layouts/guest`. Từng view chỉ đổi class và thẻ bọc ngoài, giữ nguyên chữ hiển thị.

**Tech stack:** Laravel 11 Blade, Tailwind 3.4.19 + `@tailwindcss/forms`, Alpine 3, Vite. PHPUnit trên SQLite `:memory:`.

**Spec:** [docs/superpowers/specs/2026-11-09-ui-redesign-design.md](../specs/2026-11-09-ui-redesign-design.md). Người thực thi đọc cả spec. Bảng "Phong cách" và danh sách chuỗi test trong spec là nguồn chính xác cho class và chuỗi.

## Ràng buộc chung

- Làm trên nhánh `ui-redesign` tạo từ `main`.
- Không thêm package npm hay composer. Không sửa file PHP nào ngoài `app/View/Components/`.
- Không sửa test có sẵn. Chỉ được thêm test mới.
- Không đổi chữ hiển thị đang có (test kiểm tra chuỗi). Chữ mới chỉ được thêm vào.
- Giữ nguyên `sr-only`, `aria-*`, `<legend>`, `scope="col"`, focus ring.
- `admin/questions/_form.blade.php`, `layouts/admin.blade.php` và mọi view admin không được chứa chuỗi `checked`.
- Lệnh: `docker compose exec app php artisan test`, `docker compose exec app ./vendor/bin/pint --test`, build asset bằng `docker compose run --rm assets`.
- Comment bằng tiếng Việt, chỉ ghi lý do.
- Commit theo Conventional Commits, không có dòng `Co-Authored-By`. **Chỉ commit khi người dùng yêu cầu** (CLAUDE.md).

## Điểm cần soi khi review

Test không bắt được những chỗ dưới đây. Kiểm bằng trình duyệt ở Task 6.

1. **Màn hình 375px:** không có thanh cuộn ngang. Sidebar admin ẩn, mở bằng nút ☰. Bảng admin cuộn ngang bên trong thẻ (`overflow-x-auto`).
2. **Tên đề và nội dung câu hỏi rất dài:** xuống dòng (`break-words`), không đẩy nút "Bắt đầu" hay đồng hồ ra ngoài thẻ.
3. **Chỉ dùng bàn phím trên trang làm bài:** Tab và phím mũi tên chọn được đáp án, ô đáp án có focus ring nhìn thấy được (`has-[:focus-visible]:ring-2`).
4. **Banner dính khi cuộn đề dài:** banner nằm dưới dropdown tên người dùng. Dropdown cần `z-50`, banner cần `z-10`.
5. **Danh sách rỗng** (không có đề, không có lượt xem thử): chữ thông báo rỗng vẫn hiện trong thẻ.

---

### Task 1: Component dùng chung, khung khách, trang đăng nhập và trang `/`

**Files:**
- Modify: `resources/views/components/{primary-button,secondary-button,danger-button,text-input,input-label,input-error,auth-session-status,application-logo,dropdown,dropdown-link,modal}.blade.php`
- Modify: `resources/views/layouts/guest.blade.php`, `resources/views/auth/*.blade.php`, `resources/views/welcome.blade.php`
- Modify: `resources/views/layouts/app.blade.php` (chỉ link font)
- Test: `tests/Feature/WelcomePageTest.php` (mới)

**Interfaces:**
- Produces: tên và props của component giữ nguyên. Mọi task sau dùng `<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>`, `<x-text-input>` như cũ và tự có kiểu mới.
- Produces: `<x-application-logo>` thành chữ "ExamPro" (`font-extrabold text-sky-500`). Vẫn nhận `$attributes` để nơi gọi đặt cỡ chữ.

- [ ] **Step 1: Tạo nhánh.** `git switch main && git switch -c ui-redesign`

- [ ] **Step 2: Viết test thất bại** `tests/Feature/WelcomePageTest.php` (dùng `RefreshDatabase`):
  - `test_guest_sees_login_and_register_links`: `get('/')` → `assertOk()->assertSee(route('login'))->assertSee(route('register'))->assertSee('ExamPro')`
  - `test_logged_in_user_sees_dashboard_link`: `actingAs(User::factory()->create(['role' => 'student']))->get('/')` → `assertSee(route('dashboard'))->assertDontSee(route('register'))`

- [ ] **Step 3: Chạy test để thấy nó thất bại.** `docker compose exec app php artisan test --filter=WelcomePageTest`. Kết quả mong đợi: FAIL ở `assertSee('ExamPro')`.

- [ ] **Step 4: Sửa component theo bảng "Phong cách" của spec.**
  - `primary-button` bỏ `uppercase tracking-widest`, đổi sang `rounded-full bg-slate-900 px-5 py-2 text-sm`.
  - `text-input` đổi sang `rounded-xl border-slate-300`.
  - `modal` và `dropdown` đổi sang `rounded-2xl`, dropdown thêm `z-50`.
  - Link font Bunny trong cả `layouts/app` và `layouts/guest` thêm weight `700,800`.

- [ ] **Step 5: Đổi `layouts/guest`.** Nền `bg-gradient-to-br from-sky-500 to-indigo-500`, thẻ trắng `rounded-2xl` ở giữa, logo chữ trắng phía trên. Các view `auth/*` chỉ đổi class link (`text-indigo-600`) và khoảng cách, không đổi chữ.

- [ ] **Step 6: Viết lại `welcome.blade.php`.** Trang ngắn, không dùng `<x-guest-layout>` vì cần nút theo trạng thái đăng nhập. Gồm logo, câu mô tả "Hệ thống thi trắc nghiệm trực tuyến", `@auth` nút "Vào dashboard", `@else` nút "Đăng nhập" và "Đăng ký" (chỉ hiện khi `Route::has('register')`). Nền gradient giống guest.

- [ ] **Step 7: Chạy toàn bộ test.** `docker compose exec app php artisan test`. Kết quả mong đợi: tất cả PASS (172 test cũ + 2 test mới).

- [ ] **Step 8: Commit (chỉ khi được yêu cầu).** `git add resources/views tests/Feature/WelcomePageTest.php && git commit -m "feat: restyle shared components, guest pages and welcome page"`

### Task 2: Khung học sinh, menu trên cùng, dashboard theo vai trò

**Files:**
- Modify: `resources/views/layouts/app.blade.php`, `resources/views/layouts/navigation.blade.php`, `resources/views/components/{nav-link,responsive-nav-link}.blade.php`, `resources/views/dashboard.blade.php`
- Test: `tests/Feature/DashboardPageTest.php` (mới)

**Interfaces:**
- Consumes: component của Task 1.
- Produces: `<x-app-layout>` vẫn nhận slot `header`. Header giờ hiện trong vùng nội dung (`max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8`), không còn dải trắng có bóng. View gọi layout không phải đổi gì.

- [ ] **Step 1: Viết test thất bại** `tests/Feature/DashboardPageTest.php`:
  - `test_student_dashboard_shows_student_shortcuts`: học sinh `get('/dashboard')` → `assertSee('Chào')->assertSee(route('exams.index'))->assertSee(route('packages.index'))`
  - `test_admin_dashboard_shows_admin_shortcuts`: admin `get('/dashboard')` → `assertSee(route('admin.dashboard'))->assertSee(route('packages.index'))->assertDontSee(route('exams.index'))`

- [ ] **Step 2: Chạy test để thấy nó thất bại.** `docker compose exec app php artisan test --filter=DashboardPageTest`. Kết quả mong đợi: FAIL ở `assertSee('Chào')`.

- [ ] **Step 3: Đổi `layouts/app` và `navigation`.**
  - Nền `bg-slate-50`, nav `bg-white border-b border-slate-100`.
  - `nav-link` khi đang chọn: `text-slate-900 font-semibold`, có gạch chân `border-indigo-500`. Khi không chọn: `text-slate-500`.
  - Giữ nguyên logic `isAdmin()`/`isStudent()` và menu ☰ trên điện thoại.

- [ ] **Step 4: Viết lại `dashboard.blade.php`.**
  - Banner `rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-500 text-white` có dòng "Chào {{ auth()->user()->name }} 👋".
  - Dưới banner là lưới thẻ pastel. `@if (auth()->user()->isStudent())` thì có thẻ "Làm bài" (`exams.index`). `@if (auth()->user()->isAdmin())` thì có thẻ "Quản trị" (`admin.dashboard`). Ai cũng có thẻ "Gói IELTS" (`packages.index`).
  - Bỏ chữ "You're logged in!".

- [ ] **Step 5: Chạy toàn bộ test.** `docker compose exec app php artisan test`. Kết quả mong đợi: tất cả PASS, kể cả `AdminAccessTest::test_only_admin_sees_the_admin_menu_link`.

- [ ] **Step 6: Commit (chỉ khi được yêu cầu).** `git commit -m "feat: restyle app layout, top navigation and role-aware dashboard"`

### Task 3: Khung admin với sidebar tối, chuyển các trang admin sang

**Files:**
- Create: `app/View/Components/AdminLayout.php`, `resources/views/layouts/admin.blade.php`
- Modify: `resources/views/admin/**/*.blade.php` (dashboard, exams/{index,create,edit,_form}, questions/{create,edit,_form}, statistics, preview-statistics)
- Test: `tests/Feature/Admin/AdminLayoutTest.php` (mới)

**Interfaces:**
- Produces: `App\View\Components\AdminLayout extends Component`, `render(): View` trả `view('layouts.admin')`, giống hệt `AppLayout`. Dùng bằng `<x-admin-layout>`, nhận slot `header` như `<x-app-layout>`.
- Produces: mục sidebar đang chọn dựa vào `request()->routeIs(...)`: `admin.dashboard`, `admin.exams.*` và `admin.questions.*` (cùng mục "Đề thi"), `admin.statistics`, `admin.preview-statistics`. Link "Gói IELTS" trỏ tới `packages.index`.

- [ ] **Step 1: Viết test thất bại** `tests/Feature/Admin/AdminLayoutTest.php`:
  - `test_admin_pages_use_the_sidebar`: admin `get(route('admin.exams.index'))` → `assertOk()->assertSee(route('admin.statistics'))->assertSee(route('admin.preview-statistics'))->assertSee(route('packages.index'))`
  - `test_question_form_still_has_no_checked_attribute`: admin, có một exam, `get(route('admin.exams.questions.create', $exam))` → `assertDontSee('checked', false)`. Test này cố ý lặp lại ràng buộc để nó đi cùng layout mới.

- [ ] **Step 2: Chạy test để thấy nó thất bại.** `docker compose exec app php artisan test --filter=AdminLayoutTest`. Kết quả mong đợi: FAIL ở `assertSee(route('admin.preview-statistics'))`.

- [ ] **Step 3: Tạo `AdminLayout` và `layouts/admin.blade.php`.**
  - Phần `<head>` giống `layouts/app`.
  - Thân trang là `x-data="{ open: false }"`. Sidebar `w-64 bg-slate-900 text-slate-300`: hiện cố định từ `lg` trở lên, dưới `lg` là ngăn kéo hiện bằng `x-show="open"` kèm lớp nền tối bấm vào thì đóng.
  - Mục đang chọn: `bg-gradient-to-r from-sky-500 to-indigo-500 text-white rounded-xl`.
  - Cuối sidebar có tên người dùng, link Hồ sơ (`profile.edit`) và form Đăng xuất (`POST logout`, có `@csrf`).
  - Dưới `lg` có thanh trên cùng với nút ☰ (`aria-label="Mở menu"`, `:aria-expanded="open"`).

- [ ] **Step 4: Chuyển view admin.**
  - Đổi `<x-app-layout>` thành `<x-admin-layout>` ở mọi file trong `resources/views/admin/`. Thẻ, bảng và nút đổi theo mục "Từng nhóm màn hình" của spec.
  - `exams/edit`: "(đáp án đúng)" thành badge `rounded-full bg-emerald-100 text-emerald-700`, giữ chữ.
  - `statistics`: mỗi bảng trong thẻ `rounded-2xl`, tiêu đề phần có badge pastel. Thứ tự cột và hàng giữ nguyên.
  - `preview-statistics`: thanh ngang đổi `bg-indigo-500` thành `bg-gradient-to-r from-sky-500 to-indigo-500`. Giữ `x-data="previewStats"`.

- [ ] **Step 5: Kiểm chuỗi cấm.** `grep -rn "checked" resources/views/admin resources/views/layouts/admin.blade.php`. Kết quả mong đợi: không có dòng nào.

- [ ] **Step 6: Chạy toàn bộ test và Pint.** `docker compose exec app php artisan test && docker compose exec app ./vendor/bin/pint --test`. Kết quả mong đợi: tất cả PASS, Pint không báo lỗi.

- [ ] **Step 7: Commit (chỉ khi được yêu cầu).** `git commit -m "feat: add dark sidebar admin layout and restyle admin pages"`

### Task 4: Học sinh: danh sách đề, đang làm bài, kết quả

**Files:**
- Modify: `resources/views/student/exams/index.blade.php`, `resources/views/student/attempts/show.blade.php`
- Test: `tests/Feature/Student/AttemptProgressTest.php` (mới)

**Interfaces:**
- Consumes: `<x-app-layout>` (Task 2).
- Produces: trong `x-data` của form làm bài thêm `answered` (số câu đã chọn) và `count()` gán `this.answered = this.$el.querySelectorAll('input[type=radio]:checked').length`. `init()` gọi `count()`. Mỗi radio gọi `count()` trong `@change`, trước `save(...)`. Dòng `left: {{ $remaining }},` giữ nguyên là dòng đầu tiên của `x-data`.

- [ ] **Step 1: Viết test thất bại** `tests/Feature/Student/AttemptProgressTest.php`. Tạo đề và lượt làm theo cách `AutosaveTest` đang làm.
  - `test_in_progress_page_shows_progress_bar`: `assertSee('role="progressbar"', false)->assertSee('aria-valuemax="3"', false)` với đề 3 câu.
  - `test_question_badge_shows_position`: `assertSee('Câu 1 / 3')`.

- [ ] **Step 2: Chạy test để thấy nó thất bại.** `docker compose exec app php artisan test --filter=AttemptProgressTest`. Kết quả mong đợi: FAIL.

- [ ] **Step 3: Đổi `student/exams/index`.**
  - Lưới `grid gap-4 sm:grid-cols-2`. Mỗi thẻ có badge "{n} phút", tên đề, "{n} câu", nút "Bắt đầu →".
  - Nền thẻ xoay vòng `['bg-sky-50', 'bg-violet-50', 'bg-amber-50', 'bg-emerald-50'][$loop->index % 4]`, viết đủ tên class để Tailwind quét được.
  - Giữ nguyên form POST, `@csrf` và phân trang.

- [ ] **Step 4: Đổi trang đang làm bài.**
  - Thanh dính thành banner gradient: tên đề, hạn nộp, `status` (giữ `aria-live="polite"`), đồng hồ trong `rounded-full bg-white/20 tabular-nums`.
  - Thanh tiến độ `role="progressbar"`, `aria-valuemin="0"`, `aria-valuemax="{{ count($attempt->questions) }}"`, `:aria-valuenow="answered"`, độ rộng `:style="{ width: (answered / {{ count($attempt->questions) }} * 100) + '%' }"`.
  - Mỗi câu: badge "Câu {{ $loop->iteration }} / {{ $loop->count }}". Giữ nguyên dòng `{{ $loop->iteration }}. {{ $question['content'] }}` vì test tìm "Câu 1" và nội dung câu.
  - Ô đáp án: `<label>` bọc radio thật, class `rounded-xl border-2 border-slate-200 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:focus-visible]:ring-2`. Thứ tự thuộc tính radio giữ `value="..." @checked(...)`.

- [ ] **Step 5: Đổi trang hết giờ và trang kết quả.**
  - Hết giờ: thẻ `bg-amber-50`, không có radio.
  - Kết quả: banner gradient hiện `{{ $attempt->score }}/{{ $attempt->total }}`. Mỗi câu một thẻ viền trái theo trạng thái: `border-emerald-500` đúng, `border-red-500` sai, `border-slate-300` chưa trả lời. Giữ các chữ "Câu đúng", "Câu sai", "Chưa trả lời", "(Bạn chọn)", "(Đáp án đúng)" theo đúng thứ tự cũ.

- [ ] **Step 6: Chạy toàn bộ test.** `docker compose exec app php artisan test`. Kết quả mong đợi: tất cả PASS, nhất là `AutosaveTest`, `StartAttemptTest` và `SubmitAttemptTest`.

- [ ] **Step 7: Commit (chỉ khi được yêu cầu).** `git commit -m "feat: restyle student exam list, attempt and result pages"`

### Task 5: Gói IELTS, hồ sơ, phân trang, async-state

**Files:**
- Modify: `resources/views/packages/{index,show}.blade.php`, `resources/views/components/ielts/{package-card,preview-modal,file-icon}.blade.php`, `resources/views/components/async-state.blade.php`, `resources/js/ielts/format.js` (`LEVEL_CLASSES`), `resources/views/profile/**/*.blade.php`, `resources/views/vendor/pagination/tailwind.blade.php`

**Interfaces:**
- Consumes: component Task 1, `<x-app-layout>` Task 2.
- Produces: `levelClass(level)` giữ chữ ký, trả về một trong 3 chuỗi class pastel khác nhau cho level 1, 2, 3, và chuỗi xám cho level khác.

- [ ] **Step 1: Đổi trang gói và component IELTS.**
  - Thẻ gói `rounded-2xl bg-white shadow-sm hover:shadow-md`, modal `rounded-2xl`.
  - `LEVEL_CLASSES` đổi tông nếu cần, ba màu vẫn khác nhau.
  - Giữ nguyên mọi `x-data`, `x-text`, `:class` và tên hàm Alpine.

- [ ] **Step 2: Đổi hồ sơ.** Ba form trong ba thẻ `rounded-2xl bg-white p-6 shadow-sm`. Form xóa dùng `<x-danger-button>`.

- [ ] **Step 3: Đổi phân trang.** Số trang `rounded-full`, trang hiện tại `bg-slate-900 text-white`. Giữ nguyên `href` (test cần `search=` và `page=`).

- [ ] **Step 4: Chạy toàn bộ test và build.** `docker compose exec app php artisan test && docker compose run --rm assets`. Kết quả mong đợi: test PASS, Vite build xong không lỗi.

- [ ] **Step 5: Commit (chỉ khi được yêu cầu).** `git commit -m "feat: restyle IELTS package pages, profile and pagination"`

### Task 6: Kiểm tra trên trình duyệt và README

**Files:**
- Modify: `README.md` (thêm một dòng về giao diện ở mục mô tả hoặc "Quyết định kỹ thuật", nếu README đang có chỗ hợp lý)

- [ ] **Step 1: Build và khởi động.** `docker compose run --rm assets && docker compose up -d`. Kết quả mong đợi: `/login` trả 200.

- [ ] **Step 2: Đi qua luồng ở cỡ desktop và 375px**, theo mục "Kiểm tra khi xong" của spec và 5 dòng ở mục "Điểm cần soi khi review" phía trên. Ghi lại chỗ nào hỏng rồi sửa trong view tương ứng.

- [ ] **Step 3: Kiểm tra lần cuối.** `docker compose exec app php artisan test && docker compose exec app ./vendor/bin/pint --test`. Kết quả mong đợi: tất cả PASS.

- [ ] **Step 4: Cập nhật README**, rồi commit (chỉ khi được yêu cầu): `git commit -m "docs: describe the redesigned UI"`
