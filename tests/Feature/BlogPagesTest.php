<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPagesTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $attributes = []): Post
    {
        $category = PostCategory::firstOrCreate(['slug' => 'materialy'], ['name' => ['ru' => 'Материалы', 'uz' => 'Materiallar']]);

        return Post::create($attributes + [
            'slug' => 'pu-vs-rubber',
            'post_category_id' => $category->id,
            'title' => ['ru' => 'Полиуретан или резина', 'uz' => 'Poliuretan yoki rezina'],
            'excerpt' => ['ru' => 'Сравнение материалов'],
            'body' => ['ru' => '<h2>Износ</h2><p>Полиуретан <b>служит дольше</b>.</p><script>alert(1)</script>'],
            'is_published' => true,
        ]);
    }

    public function test_the_list_shows_published_posts(): void
    {
        $this->makePost();
        $this->makePost(['slug' => 'draft', 'title' => ['ru' => 'Черновик статьи'], 'is_published' => false, 'post_category_id' => null]);

        $this->get('/blog')
            ->assertOk()
            ->assertSee('Полиуретан или резина', false)
            ->assertSee('/blog/pu-vs-rubber', false)
            ->assertDontSee('Черновик статьи', false);
    }

    public function test_an_article_opens_with_a_clean_body_and_a_table_of_contents(): void
    {
        $this->makePost();

        $this->get('/blog/pu-vs-rubber')
            ->assertOk()
            ->assertSee('<title>Полиуретан или резина — Inter Roll</title>', false)
            ->assertSee('служит дольше', false)
            ->assertSee('"toc":[{"href":"#iznos"', false)
            ->assertDontSee('alert(1)', false);

        $this->get('/uz/blog/pu-vs-rubber')->assertOk()->assertSee('Poliuretan yoki rezina', false);
    }

    public function test_a_post_dated_in_the_future_waits(): void
    {
        $this->makePost(['published_at' => now()->addWeek()]);

        $this->get('/blog/pu-vs-rubber')->assertNotFound();
    }

    public function test_the_designed_shore_a_article_works_with_and_without_a_post(): void
    {
        $this->get('/blog/shore-a-hardness')->assertOk();
        $this->get('/blog/shore-a')->assertStatus(301)->assertRedirect('/blog/shore-a-hardness');

        $this->seed(ContentSeeder::class);
        Post::firstWhere('slug', 'shore-a-hardness')->update(['is_published' => true, 'author' => ['ru' => 'Иван Петров']]);

        $this->get('/blog/shore-a-hardness')->assertOk()->assertSee('Иван Петров', false);
    }

    public function test_unknown_articles_are_not_found(): void
    {
        $this->get('/blog/nothing-here')->assertNotFound();
    }
}
