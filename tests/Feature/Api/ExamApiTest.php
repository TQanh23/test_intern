<?php

namespace Tests\Feature\Api;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExamApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_gets_401_json_without_accept_header(): void
    {
        // Gọi get() thay vì getJson() để giống client không gửi header Accept.
        $this->get('/api/exams')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_admin_gets_403(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->getJson('/api/exams')->assertForbidden();
    }

    public function test_student_gets_paginated_exams_with_resource_fields(): void
    {
        $exam = Exam::create(['name' => 'Toán', 'duration_minutes' => 30]);
        $question = $exam->questions()->create(['content' => 'Câu 1']);
        $question->options()->create(['content' => 'Đúng', 'is_correct' => true]);
        Exam::create(['name' => 'Đề trống', 'duration_minutes' => 10]);

        Sanctum::actingAs(User::factory()->create(['role' => 'student']));

        // Đề trống bị bỏ, và mỗi đề chỉ có 4 trường của ExamResource, không có câu hỏi hay đáp án.
        $this->getJson('/api/exams')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonPath('data', [
                ['id' => $exam->id, 'name' => 'Toán', 'duration_minutes' => 30, 'questions_count' => 1],
            ])
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.per_page', 10);
    }
}
