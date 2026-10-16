<?php

namespace Tests\Feature;

use App\Models\Industry;
use App\Models\Project;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsPageTest extends TestCase
{
    use RefreshDatabase;

    private function project(array $attributes = []): Project
    {
        $industry = Industry::create(['slug' => 'metallurgiya', 'name' => ['ru' => 'Металлургия', 'uz' => 'Metallurgiya']]);

        return Project::create($attributes + [
            'slug' => 'prokat',
            'industry_id' => $industry->id,
            'title' => ['ru' => 'Восстановление валов прокатного стана', 'uz' => 'Prokat valiklarini qayta qoplash'],
            'kpis' => [['value' => '3×', 'label' => ['ru' => 'дольше служит']]],
            'is_published' => true,
        ]);
    }

    public function test_published_projects_and_their_industries_reach_the_page(): void
    {
        $this->project();

        $this->get('/projects')
            ->assertOk()
            ->assertSee('Восстановление валов прокатного стана', false)
            ->assertSee('Металлургия', false)
            ->assertSee('3×', false);

        $this->get('/uz/projects')->assertSee('Prokat valiklarini qayta qoplash', false);
    }

    public function test_hidden_projects_are_not_shown(): void
    {
        $this->project(['is_published' => false]);

        $this->get('/projects')->assertOk()->assertDontSee('Восстановление валов прокатного стана', false);
    }

    public function test_an_admin_change_shows_up_at_once(): void
    {
        $project = $this->project();
        $this->get('/projects')->assertSee('Восстановление валов прокатного стана', false);

        $project->update(['title' => ['ru' => 'Новые валы для прокатного стана']]);

        $this->get('/projects')->assertSee('Новые валы для прокатного стана', false);
    }

    public function test_the_design_cases_are_seeded_as_hidden_drafts(): void
    {
        $this->seed(CatalogSeeder::class);
        $this->seed(ContentSeeder::class);
        $this->seed(ContentSeeder::class);

        $this->assertSame(6, Project::count());
        $this->assertSame(0, Project::query()->published()->count());
        $this->assertNotNull(Project::firstWhere('slug', 'ofset-boyoq-valiklari')->industry_id);
    }
}
