<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display font-bold text-2xl tracking-tight text-ink sm:text-3xl leading-tight break-words">{{ $attempt->exam_name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status :status="session('status')" />
            <x-input-error :messages="$errors->get('attempt')" />

            @if ($attempt->status === 'submitted')
                @php
                    // Đếm ba nhóm câu cho các thẻ số liệu và ma trận câu hỏi.
                    $correctCount = collect($attempt->questions)->where('is_correct', true)->count();
                    $blankCount = collect($attempt->questions)->whereNull('selected_option_id')->count();
                    $wrongCount = count($attempt->questions) - $correctCount - $blankCount;
                @endphp

                {{-- Thẻ điểm + ba thẻ số liệu, theo màn "Trang kết quả bài thi" trong Figma --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl bg-ink p-5 text-white shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand-light">Kết quả</p>
                        <p class="mt-1 font-display text-4xl font-bold tabular-nums">{{ $attempt->score }}/{{ $attempt->total }}</p>
                        <p class="mt-1 text-sm text-white/80">câu đúng</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Số câu đúng</p>
                        <p class="mt-1 font-display text-3xl font-bold tabular-nums text-emerald-600">{{ $correctCount }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Số câu sai</p>
                        <p class="mt-1 font-display text-3xl font-bold tabular-nums text-red-600">{{ $wrongCount }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Bỏ trống</p>
                        <p class="mt-1 font-display text-3xl font-bold tabular-nums text-slate-500">{{ $blankCount }}</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start">
                    <div class="space-y-4">
                        @foreach ($attempt->questions as $question)
                            @php($answered = $question['selected_option_id'] !== null)
                            {{-- Viền trái theo trạng thái: đúng xanh lá, sai đỏ, chưa trả lời xám. --}}
                            <div id="cau-{{ $loop->iteration }}" @class(['scroll-mt-24 bg-white rounded-xl shadow-sm p-6 border-l-4', 'border-emerald-500' => $question['is_correct'], 'border-red-500' => ! $question['is_correct'] && $answered, 'border-slate-300' => ! $question['is_correct'] && ! $answered])>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span @class(['rounded-md px-2.5 py-0.5 text-xs font-bold text-white', 'bg-emerald-600' => $question['is_correct'], 'bg-red-600' => ! $question['is_correct'] && $answered, 'bg-slate-400' => ! $question['is_correct'] && ! $answered])>{{ $loop->iteration }}</span>
                                    <span @class(['rounded-md px-2.5 py-0.5 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $question['is_correct'], 'bg-red-50 text-red-600' => ! $question['is_correct'] && $answered, 'bg-surface-low text-slate-600' => ! $question['is_correct'] && ! $answered])>
                                        @if ($question['is_correct'])
                                            Câu đúng
                                        @elseif (! $answered)
                                            Chưa trả lời
                                        @else
                                            Câu sai
                                        @endif
                                    </span>
                                </div>
                                <p class="mt-3 font-bold text-ink break-words">{{ $loop->iteration }}. {{ $question['content'] }}</p>

                                {{-- Phương án bạn chọn mà sai: nền đỏ gạch ngang. Đáp án đúng: nền xanh. --}}
                                <ul class="mt-4 space-y-2 text-slate-600">
                                    @foreach ($question['options'] as $option)
                                        @php($picked = $option['option_id'] === $question['selected_option_id'])
                                        <li @class(['flex flex-wrap items-center gap-2 rounded-lg border px-4 py-2.5 break-words', 'border-emerald-200 bg-emerald-50 font-semibold text-emerald-800' => $option['is_correct'], 'border-red-200 bg-red-50 text-red-700' => $picked && ! $option['is_correct'], 'border-surface-high' => ! $picked && ! $option['is_correct']])>
                                            <span @class(['min-w-0', 'line-through' => $picked && ! $option['is_correct']])>{{ $option['content'] }}</span>
                                            @if ($picked)
                                                <span class="rounded-md bg-white px-2 py-0.5 text-xs font-semibold no-underline">Bạn chọn</span>
                                            @endif
                                            @if ($option['is_correct'])
                                                <span class="rounded-md bg-emerald-600 px-2 py-0.5 text-xs font-semibold text-white">Đáp án đúng</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>

                    {{-- Ma trận câu hỏi: bấm số để nhảy tới câu đó --}}
                    <aside class="rounded-xl bg-white p-5 shadow-sm lg:sticky lg:top-24">
                        <h3 class="font-bold text-ink">Ma trận câu hỏi</h3>
                        <div class="mt-4 grid grid-cols-6 gap-2">
                            @foreach ($attempt->questions as $question)
                                <a href="#cau-{{ $loop->iteration }}" @class(['flex h-9 items-center justify-center rounded-lg text-sm font-semibold', 'bg-emerald-100 text-emerald-800' => $question['is_correct'], 'bg-red-100 text-red-700' => ! $question['is_correct'] && $question['selected_option_id'] !== null, 'bg-surface-low text-slate-500' => ! $question['is_correct'] && $question['selected_option_id'] === null])>{{ $loop->iteration }}</a>
                            @endforeach
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3 text-xs text-slate-600">
                            <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-emerald-100"></span>Đúng</span>
                            <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-red-100"></span>Sai</span>
                            <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-surface-low"></span>Bỏ trống</span>
                        </div>
                    </aside>
                </div>
            @elseif ($attempt->isExpired())
                <div class="bg-amber-50 rounded-xl shadow-sm p-6 space-y-4">
                    <p class="text-ink">Đã hết giờ làm bài. Bài sẽ được chấm theo các đáp án đã lưu.</p>
                    <form method="POST" action="{{ route('attempts.submit', $attempt) }}">
                        @csrf
                        <x-primary-button>Chấm bài</x-primary-button>
                    </form>
                </div>
            @else
                @php($remaining = $attempt->remainingSeconds())

                {{-- Đếm ngược từ số giây còn lại do server tính, nên giờ máy client sai cũng không ảnh hưởng. --}}
                <form method="POST" action="{{ route('attempts.submit', $attempt) }}" class="space-y-6"
                    x-data="{
                        left: {{ $remaining }},
                        end: 0,
                        status: '',
                        answered: 0,
                        picked: {},
                        init() {
                            this.count();
                            // Tính lại theo mốc kết thúc mỗi giây, nên tab chạy nền bị chậm cũng không lệch giờ.
                            this.end = Date.now() + this.left * 1000;
                            const timer = setInterval(() => {
                                this.left = Math.max(0, Math.ceil((this.end - Date.now()) / 1000));
                                if (this.left === 0) {
                                    clearInterval(timer);
                                    this.$el.submit();
                                }
                            }, 1000);
                        },
                        // Ghi lại câu nào đã chọn để vẽ thanh tiến độ và bảng câu hỏi (không gửi đi đâu).
                        count() {
                            const checked = this.$el.querySelectorAll('input[type=radio]:checked');
                            this.picked = {};
                            checked.forEach((radio) => this.picked[radio.dataset.question] = true);
                            this.answered = checked.length;
                        },
                        clock() {
                            return Math.floor(this.left / 60) + ':' + String(this.left % 60).padStart(2, '0');
                        },
                        queue: Promise.resolve(),
                        save(questionId, optionId) {
                            this.status = 'Đang lưu...';
                            // Gửi lần lượt: request sau chờ request trước xong, để request cũ đến muộn không ghi đè đáp án mới.
                            this.queue = this.queue
                                .then(() => axios.patch('{{ route('attempts.answers', $attempt) }}', { question_id: questionId, option_id: optionId }))
                                .then(() => this.status = 'Đã lưu')
                                .catch(() => this.status = 'Chưa lưu được, hãy chọn lại đáp án.');
                        },
                    }">
                    @csrf

                    {{-- Thanh trên cùng: tên đề, trạng thái lưu, đồng hồ navy và nút nộp (theo màn "Làm bài" trong Figma) --}}
                    <div class="sticky top-16 z-10 rounded-xl bg-white px-5 py-4 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-ink break-words">{{ $attempt->exam_name }}</p>
                                <p class="text-sm text-slate-600">Hạn nộp: {{ $attempt->deadline_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <p class="rounded-lg bg-ink px-4 py-2 text-white tabular-nums">
                                    <span class="text-xs text-white/70">Còn lại</span>
                                    <span class="ms-1 font-display text-lg font-bold" x-text="clock()">{{ sprintf('%d:%02d', intdiv($remaining, 60), $remaining % 60) }}</span>
                                </p>
                            </div>
                        </div>
                        <p class="mt-2 text-sm font-medium text-brand" aria-live="polite" x-text="status"></p>
                        <div class="mt-2 h-2 rounded-full bg-surface-low" role="progressbar" aria-label="Số câu đã chọn" aria-valuemin="0" aria-valuemax="{{ count($attempt->questions) }}" :aria-valuenow="answered">
                            <div class="h-2 rounded-full bg-brand transition-all" :style="{ width: (answered / {{ count($attempt->questions) }} * 100) + '%' }"></div>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start">
                    <div class="space-y-6">
                    @foreach ($attempt->questions as $question)
                        <fieldset id="cau-{{ $loop->iteration }}" class="scroll-mt-48 bg-white rounded-xl shadow-sm p-6 min-w-0">
                            <legend class="sr-only">Câu {{ $loop->iteration }}</legend>
                            <span class="rounded-md bg-surface-high px-2.5 py-0.5 text-xs font-semibold text-brand">Câu {{ $loop->iteration }} / {{ $loop->count }}</span>
                            <p class="mt-3 font-bold text-ink break-words">{{ $loop->iteration }}. {{ $question['content'] }}</p>

                            <div class="mt-4 space-y-2">
                                @foreach ($question['options'] as $option)
                                    <label class="flex items-center gap-3 rounded-lg border border-surface-high px-4 py-3 text-slate-600 cursor-pointer hover:bg-surface-low has-[:checked]:border-brand has-[:checked]:bg-brand-pale has-[:checked]:text-ink has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand">
                                        <input type="radio" name="answers[{{ $question['question_id'] }}]" data-question="{{ $question['question_id'] }}" value="{{ $option['option_id'] }}" @checked($option['option_id'] === $question['selected_option_id'])
                                            @change="count(); save({{ $question['question_id'] }}, {{ $option['option_id'] }})"
                                            class="shrink-0 border-slate-300 text-brand focus:ring-brand">
                                        <span class="min-w-0 break-words">{{ $option['content'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                    </div>

                    {{-- Bảng câu hỏi: ô navy là câu đã chọn đáp án, bấm số để nhảy tới câu đó --}}
                    <aside class="rounded-xl bg-white p-5 shadow-sm lg:sticky lg:top-48">
                        <h3 class="font-bold text-ink">Bảng câu hỏi</h3>
                        <p class="mt-1 text-xs text-slate-500"><span x-text="answered">0</span> / {{ count($attempt->questions) }} câu đã làm</p>
                        <div class="mt-4 grid grid-cols-5 gap-2">
                            @foreach ($attempt->questions as $question)
                                <a href="#cau-{{ $loop->iteration }}"
                                    :class="picked[{{ $question['question_id'] }}] ? 'bg-ink text-white' : 'bg-surface-low text-slate-600 hover:bg-surface-high'"
                                    class="flex h-9 items-center justify-center rounded-lg text-sm font-semibold">{{ $loop->iteration }}</a>
                            @endforeach
                        </div>
                        <x-primary-button class="mt-5 w-full">Nộp bài</x-primary-button>
                    </aside>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
