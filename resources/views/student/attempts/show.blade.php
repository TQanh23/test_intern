<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-slate-900 leading-tight break-words">{{ $attempt->exam_name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status :status="session('status')" />
            <x-input-error :messages="$errors->get('attempt')" />

            @if ($attempt->status === 'submitted')
                <div class="bg-gradient-to-br from-sky-700 to-indigo-600 text-white rounded-2xl p-6">
                    <p class="text-sm font-semibold">Kết quả</p>
                    <p class="mt-1 text-4xl font-extrabold tabular-nums">{{ $attempt->score }}/{{ $attempt->total }}</p>
                    <p class="mt-1 text-sm">câu đúng</p>
                </div>

                @foreach ($attempt->questions as $question)
                    {{-- Viền trái theo trạng thái: đúng xanh lá, sai đỏ, chưa trả lời xám. --}}
                    <div @class(['bg-white rounded-2xl shadow-sm p-6 border-l-4', 'border-emerald-500' => $question['is_correct'], 'border-red-500' => ! $question['is_correct'] && $question['selected_option_id'] !== null, 'border-slate-300' => ! $question['is_correct'] && $question['selected_option_id'] === null])>
                        <p class="font-bold text-slate-900 break-words">{{ $loop->iteration }}. {{ $question['content'] }}</p>

                        <p @class(['mt-1 text-sm font-semibold', 'text-emerald-700' => $question['is_correct'], 'text-red-600' => ! $question['is_correct']])>
                            @if ($question['is_correct'])
                                Câu đúng
                            @elseif ($question['selected_option_id'] === null)
                                Chưa trả lời
                            @else
                                Câu sai
                            @endif
                        </p>

                        <ul class="mt-4 space-y-2 text-slate-700">
                            @foreach ($question['options'] as $option)
                                <li @class(['break-words font-semibold text-emerald-700' => $option['is_correct']])>
                                    {{ $option['content'] }}
                                    @if ($option['option_id'] === $question['selected_option_id'])
                                        (Bạn chọn)
                                    @endif
                                    @if ($option['is_correct'])
                                        (Đáp án đúng)
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @elseif ($attempt->isExpired())
                <div class="bg-amber-50 rounded-2xl shadow-sm p-6 space-y-4">
                    <p class="text-slate-900">Đã hết giờ làm bài. Bài sẽ được chấm theo các đáp án đã lưu.</p>
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
                        // Đếm số radio đang được chọn, chỉ để vẽ thanh tiến độ (không gửi đi đâu).
                        count() {
                            this.answered = this.$el.querySelectorAll('input[type=radio]:checked').length;
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

                    <div class="sticky top-0 z-10 bg-gradient-to-br from-sky-700 to-indigo-600 text-white rounded-2xl px-6 py-4 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-extrabold break-words">{{ $attempt->exam_name }}</p>
                                <p class="text-sm">Hạn nộp: {{ $attempt->deadline_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') }}</p>
                            </div>
                            <p class="rounded-full bg-slate-900/20 px-4 py-1 tabular-nums">
                                Còn lại: <span class="font-bold" x-text="clock()">{{ sprintf('%d:%02d', intdiv($remaining, 60), $remaining % 60) }}</span>
                            </p>
                        </div>
                        <p class="text-sm" aria-live="polite" x-text="status"></p>
                        <div class="h-2 rounded-full bg-white/30" role="progressbar" aria-label="Số câu đã chọn" aria-valuemin="0" aria-valuemax="{{ count($attempt->questions) }}" :aria-valuenow="answered">
                            <div class="h-2 rounded-full bg-white transition-all" :style="{ width: (answered / {{ count($attempt->questions) }} * 100) + '%' }"></div>
                        </div>
                    </div>

                    @foreach ($attempt->questions as $question)
                        <fieldset class="bg-white rounded-2xl shadow-sm p-6 min-w-0">
                            <legend class="sr-only">Câu {{ $loop->iteration }}</legend>
                            <span class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700">Câu {{ $loop->iteration }} / {{ $loop->count }}</span>
                            <p class="mt-3 font-bold text-slate-900 break-words">{{ $loop->iteration }}. {{ $question['content'] }}</p>

                            <div class="mt-4 space-y-2">
                                @foreach ($question['options'] as $option)
                                    <label class="flex items-center gap-3 rounded-xl border-2 border-slate-200 px-4 py-3 text-slate-700 cursor-pointer hover:bg-slate-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-indigo-500">
                                        <input type="radio" name="answers[{{ $question['question_id'] }}]" value="{{ $option['option_id'] }}" @checked($option['option_id'] === $question['selected_option_id'])
                                            @change="count(); save({{ $question['question_id'] }}, {{ $option['option_id'] }})"
                                            class="shrink-0 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="min-w-0 break-words">{{ $option['content'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach

                    <div>
                        <x-primary-button>Nộp bài</x-primary-button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
