<x-admin-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-wider text-brand">Dữ liệu bài thi từ MongoDB</p>
        <h2 class="font-display font-bold text-2xl tracking-tight text-ink sm:text-3xl">Thống kê</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <p class="text-sm text-slate-600">
                Chỉ tính bài đã nộp. Tỷ lệ đúng = số câu đúng / tổng số câu của bài.
                <a href="{{ route('admin.preview-statistics') }}" class="font-semibold text-brand hover:text-brand-dark">Xem lượt xem thử gói IELTS →</a>
            </p>

            <div class="grid gap-6 xl:grid-cols-2 xl:items-start">
            <section>
                <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                    <div class="border-b border-surface-high px-6 py-4">
                        <h3 class="font-bold text-ink">Tỷ lệ đúng trung bình theo đề</h3>
                        <p class="text-xs text-slate-500">Gom nhóm bài nộp theo đề</p>
                    </div>
                    @if ($exams->isEmpty())
                        <p class="p-6 text-slate-600">Chưa có bài nộp nào.</p>
                    @else
                        <table class="min-w-full divide-y divide-surface-high text-sm">
                            <thead class="bg-surface-low text-left text-xs uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Đề</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Số bài nộp</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Tỷ lệ đúng TB</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-high">
                                @foreach ($exams as $exam)
                                    <tr class="hover:bg-surface">
                                        <td class="px-6 py-4 font-medium text-ink">{{ $exam['exam_name'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $exam['attempts'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ round($exam['avg_percent'], 1) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <section>
                <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                    <div class="border-b border-surface-high px-6 py-4">
                        <h3 class="font-bold text-ink">Top 10 học sinh</h3>
                        <p class="text-xs text-slate-500">Xếp theo tỷ lệ đúng trung bình</p>
                    </div>
                    @if ($students->isEmpty())
                        <p class="p-6 text-slate-600">Chưa có bài nộp nào.</p>
                    @else
                        <table class="min-w-full divide-y divide-surface-high text-sm">
                            <thead class="bg-surface-low text-left text-xs uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Hạng</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Học sinh</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Số bài nộp</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Tỷ lệ đúng TB</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-high">
                                @foreach ($students as $student)
                                    <tr class="hover:bg-surface">
                                        <td class="px-6 py-4">
                                            {{-- Ba hạng đầu nền navy, còn lại nền xanh nhạt --}}
                                            <span @class(['inline-flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold', 'bg-ink text-white' => $loop->iteration <= 3, 'bg-surface-low text-slate-600' => $loop->iteration > 3])>{{ $loop->iteration }}</span>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-ink">{{ $student['name'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $student['attempts'] }}</td>
                                        <td class="px-6 py-4 font-bold text-brand">{{ round($student['avg_percent'], 1) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            </div>

            <section>
                <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                    <div class="border-b border-surface-high px-6 py-4">
                        <h3 class="font-bold text-ink">10 câu sai nhiều nhất</h3>
                        <p class="text-xs text-slate-500">Số lần sai trên số lần câu xuất hiện trong bài</p>
                    </div>
                    @if ($questions->isEmpty())
                        <p class="p-6 text-slate-600">Chưa có bài nộp nào.</p>
                    @else
                        <table class="min-w-full divide-y divide-surface-high text-sm">
                            <thead class="bg-surface-low text-left text-xs uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Câu hỏi</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Đề</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Sai / số lần làm</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-high">
                                @foreach ($questions as $question)
                                    <tr class="hover:bg-surface">
                                        <td class="px-6 py-4 font-medium text-ink">{{ $question['content'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $question['exam_name'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap"><span class="rounded-md bg-red-50 px-2 py-0.5 font-semibold text-red-600">{{ $question['wrong'] }}/{{ $question['appearances'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
