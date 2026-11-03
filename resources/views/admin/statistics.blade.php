<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Thống kê</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <p class="px-4 sm:px-0 text-sm text-gray-600">Chỉ tính bài đã nộp. Tỷ lệ đúng = số câu đúng / tổng số câu của bài.</p>

            <section class="space-y-3">
                <h3 class="px-4 sm:px-0 font-semibold text-gray-800">Tỷ lệ đúng trung bình theo đề</h3>
                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    @if ($exams->isEmpty())
                        <p class="p-6 text-gray-600">Chưa có bài nộp nào.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Đề</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Số bài nộp</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Tỷ lệ đúng TB</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($exams as $exam)
                                    <tr>
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $exam['exam_name'] }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $exam['attempts'] }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ round($exam['avg_percent'], 1) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <section class="space-y-3">
                <h3 class="px-4 sm:px-0 font-semibold text-gray-800">Top 10 học sinh</h3>
                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    @if ($students->isEmpty())
                        <p class="p-6 text-gray-600">Chưa có bài nộp nào.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Hạng</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Học sinh</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Số bài nộp</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Tỷ lệ đúng TB</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($students as $student)
                                    <tr>
                                        <td class="px-6 py-4 text-gray-600">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $student['name'] }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $student['attempts'] }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ round($student['avg_percent'], 1) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <section class="space-y-3">
                <h3 class="px-4 sm:px-0 font-semibold text-gray-800">10 câu sai nhiều nhất</h3>
                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    @if ($questions->isEmpty())
                        <p class="p-6 text-gray-600">Chưa có bài nộp nào.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Câu hỏi</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Đề</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Sai / số lần làm</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($questions as $question)
                                    <tr>
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $question['content'] }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $question['exam_name'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $question['wrong'] }}/{{ $question['appearances'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
