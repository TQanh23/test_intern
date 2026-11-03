<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $attempt->exam_name }}</h2>
            <p class="text-sm text-gray-600">
                Hạn nộp: {{ $attempt->deadline_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') }}
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status class="px-4 sm:px-0" :status="session('status')" />
            <x-input-error class="px-4 sm:px-0" :messages="$errors->get('attempt')" />

            @if ($attempt->status === 'submitted')
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-900">Kết quả: <span class="font-semibold">{{ $attempt->score }}/{{ $attempt->total }}</span> câu đúng.</p>
                </div>

                @foreach ($attempt->questions as $question)
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <p class="font-medium text-gray-900">{{ $loop->iteration }}. {{ $question['content'] }}</p>

                        <p @class(['mt-1 text-sm font-semibold', 'text-green-700' => $question['is_correct'], 'text-red-600' => ! $question['is_correct']])>
                            @if ($question['is_correct'])
                                Câu đúng
                            @elseif ($question['selected_option_id'] === null)
                                Chưa trả lời
                            @else
                                Câu sai
                            @endif
                        </p>

                        <ul class="mt-4 space-y-2 text-gray-700">
                            @foreach ($question['options'] as $option)
                                <li @class(['font-semibold text-green-700' => $option['is_correct']])>
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
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <p class="text-gray-900">Đã hết giờ làm bài. Bài sẽ được chấm theo các đáp án đã lưu.</p>
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
                        init() {
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

                    <div class="sticky top-0 z-10 bg-white shadow-sm sm:rounded-lg px-6 py-3 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-gray-900">
                            Còn lại: <span class="font-semibold tabular-nums" x-text="clock()">{{ sprintf('%d:%02d', intdiv($remaining, 60), $remaining % 60) }}</span>
                        </p>
                        <p class="text-sm text-gray-600" aria-live="polite" x-text="status"></p>
                    </div>

                    @foreach ($attempt->questions as $question)
                        <fieldset class="bg-white shadow-sm sm:rounded-lg p-6">
                            <legend class="sr-only">Câu {{ $loop->iteration }}</legend>
                            <p class="font-medium text-gray-900">{{ $loop->iteration }}. {{ $question['content'] }}</p>

                            <div class="mt-4 space-y-2">
                                @foreach ($question['options'] as $option)
                                    <label class="flex items-center gap-3 text-gray-700">
                                        <input type="radio" name="answers[{{ $question['question_id'] }}]" value="{{ $option['option_id'] }}" @checked($option['option_id'] === $question['selected_option_id'])
                                            @change="save({{ $question['question_id'] }}, {{ $option['option_id'] }})"
                                            class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ $option['content'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach

                    <div class="px-4 sm:px-0">
                        <x-primary-button>Nộp bài</x-primary-button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
