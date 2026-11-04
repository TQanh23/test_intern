<?php

namespace Tests\Feature\Api;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PackageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_gets_401(): void
    {
        $this->getJson('/api/packages')->assertUnauthorized();
    }

    public function test_list_filters_by_level_and_paginates(): void
    {
        Package::create(['name' => 'Level 1', 'slug' => 'level-1', 'level' => 1]);
        $level2 = Package::create(['name' => 'Level 2', 'slug' => 'level-2', 'level' => 2]);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/packages?level=2')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $level2->id)
            ->assertJsonPath('data.0.slug', 'level-2')
            ->assertJsonMissingPath('data.0.units');

        $this->getJson('/api/packages')->assertJsonPath('meta.total', 2);
    }

    public function test_list_searches_by_name(): void
    {
        Package::create(['name' => 'Mindset Level 1', 'slug' => 'level-1', 'level' => 1]);
        $other = Package::create(['name' => 'Grammar Basics', 'slug' => 'grammar', 'level' => 1]);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/packages?q=grammar')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $other->id);
    }

    public function test_list_has_price_cover_and_document_count(): void
    {
        $package = Package::create([
            'name' => 'Level 1', 'slug' => 'level-1', 'level' => 1,
            'price' => 199000, 'cover_image' => 'images/packages/level-1.svg',
        ]);
        // 2 unit, mỗi unit 2 tài liệu: số tài liệu phải đếm qua unit, không phải số unit.
        foreach ([1, 2] as $position) {
            $unit = $package->units()->create(['title' => "Unit {$position}", 'position' => $position]);
            $unit->documents()->create(['title' => 'Reading', 'type' => 'pdf', 'file_path' => 'a.pdf', 'page_count' => 10, 'preview_pages' => 3]);
            $unit->documents()->create(['title' => 'Listening', 'type' => 'audio', 'file_path' => 'a.wav']);
        }
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/packages')
            ->assertOk()
            ->assertJsonPath('data.0.price', 199000)
            ->assertJsonPath('data.0.cover_url', asset('images/packages/level-1.svg'))
            ->assertJsonPath('data.0.documents_count', 4);
    }

    public function test_package_without_cover_has_null_cover_url(): void
    {
        Package::create(['name' => 'Level 1', 'slug' => 'level-1', 'level' => 1]);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/packages')
            ->assertJsonPath('data.0.cover_url', null)
            ->assertJsonPath('data.0.price', 0);
    }

    public function test_invalid_level_gets_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/packages?level=9')->assertUnprocessable()->assertJsonValidationErrors('level');
    }

    public function test_detail_lists_units_and_documents_in_order(): void
    {
        $package = Package::create(['name' => 'Level 1', 'slug' => 'level-1', 'level' => 1]);
        $unit2 = $package->units()->create(['title' => 'Unit 2', 'position' => 2]);
        $unit1 = $package->units()->create(['title' => 'Unit 1', 'position' => 1]);
        $unit1->documents()->create([
            'title' => 'Reading', 'type' => 'pdf', 'file_path' => 'documents/a.pdf', 'page_count' => 10, 'preview_pages' => 3,
        ]);
        $unit1->documents()->create(['title' => 'Listening', 'type' => 'audio', 'file_path' => 'documents/a.wav']);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/packages/level-1')
            ->assertOk()
            ->assertJsonPath('data.units.0.id', $unit1->id)
            ->assertJsonPath('data.units.1.id', $unit2->id)
            ->assertJsonPath('data.units.0.documents.0', [
                'id' => $unit1->documents()->where('type', 'pdf')->value('id'),
                'title' => 'Reading', 'type' => 'pdf', 'page_count' => 10, 'preview_pages' => 3, 'preview_seconds' => null,
            ])
            ->assertJsonPath('data.units.0.documents.1.title', 'Listening')
            ->assertJsonPath('data.units.0.documents.1.preview_seconds', 15)
            ->assertJsonPath('data.units.1.documents', []);
    }

    public function test_unknown_slug_gets_404(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/packages/khong-co')->assertNotFound();
    }
}
