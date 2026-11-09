<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-slate-900">Thống kê</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <p class="text-sm text-slate-600">
                Chỉ tính bài đã nộp. Tỷ lệ đúng = số câu đúng / tổng số câu của bài.
                <a href="{{ route('admin.preview-statistics') }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Xem lượt xem thử gói IELTS →</a>
            </p>

            <section class="space-y-3">
                <h3><span class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700">Tỷ lệ đúng trung bình theo đề</span></h3>
                <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
                    @if ($exams->isEmpty())
                        <p class="p-6 text-slate-600">Chưa có bài nộp nào.</p>
                    @else
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="text-left text-slate-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Đề</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Số bài nộp</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Tỷ lệ đúng TB</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($exams as $exam)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ $exam['exam_name'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $exam['attempts'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ round($exam['avg_percent'], 1) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <section class="space-y-3">
                <h3><span class="rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700">Top 10 học sinh</span></h3>
                <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
                    @if ($students->isEmpty())
                        <p class="p-6 text-slate-600">Chưa có bài nộp nào.</p>
                    @else
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="text-left text-slate-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Hạng</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Học sinh</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Số bài nộp</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Tỷ lệ đúng TB</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($students as $student)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 text-slate-600">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ $student['name'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $student['attempts'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ round($student['avg_percent'], 1) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <section class="space-y-3">
                <h3><span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">10 câu sai nhiều nhất</span></h3>
                <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
                    @if ($questions->isEmpty())
                        <p class="p-6 text-slate-600">Chưa có bài nộp nào.</p>
                    @else
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="text-left text-slate-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Câu hỏi</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Đề</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Sai / số lần làm</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($questions as $question)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ $question['content'] }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $question['exam_name'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $question['wrong'] }}/{{ $question['appearances'] }}</td>
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
