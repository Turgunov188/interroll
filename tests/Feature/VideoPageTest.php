<?php

namespace Tests\Feature;

use App\Enums\VideoKind;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_videos_reach_the_player_with_chapters(): void
    {
        Video::create([
            'kind' => VideoKind::Main,
            'title' => ['ru' => 'Экскурсия по заводу'],
            'url' => 'https://youtu.be/dQw4w9WgXcQ',
            'duration' => '3:20',
            'chapters' => [['at' => '1:40', 'label' => ['ru' => 'Литьё']]],
            'is_published' => true,
        ]);
        Video::create(['kind' => VideoKind::Short, 'title' => ['ru' => 'Заливка бандажа'], 'url' => 'https://youtube.com/shorts/dQw4w9WgXcQ', 'is_published' => true]);
        Video::create(['kind' => VideoKind::Main, 'title' => ['ru' => 'Скрытое видео'], 'is_published' => false]);

        $response = $this->get('/video')->assertOk();

        $video = $response->viewData('videos')[0];
        $this->assertSame('dQw4w9WgXcQ', $video->youtubeId);
        $this->assertSame([['at' => '1:40', 'label' => 'Литьё', 'position' => 0.5, 'seconds' => 100]], $video->chapters);
        $this->assertSame('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $video->poster);
        $this->assertCount(1, $response->viewData('videos'));

        $response
            ->assertSee('Экскурсия по заводу', false)
            ->assertSee('Заливка бандажа', false)
            ->assertDontSee('Скрытое видео', false)
            ->assertDontSee('youtube-nocookie.com/embed/dQw', false); // nothing loads from YouTube before play
    }

    public function test_youtube_links_of_every_kind_are_recognised(): void
    {
        foreach (['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://youtu.be/dQw4w9WgXcQ?t=5', 'https://youtube.com/shorts/dQw4w9WgXcQ'] as $url) {
            $this->assertSame('dQw4w9WgXcQ', (new Video(['url' => $url]))->youtubeId());
        }

        $this->assertNull((new Video(['url' => 'https://instagram.com/p/xyz']))->youtubeId());
        $this->assertSame(3725, Video::seconds('1:02:05'));
    }
}
