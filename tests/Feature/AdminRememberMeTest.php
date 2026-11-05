<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminRememberMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_remember_cookie_expires_after_one_day(): void
    {
        $user = User::factory()->create();

        $this->freezeTime();
        Auth::guard('web')->login($user, remember: true);

        $cookie = collect(app('cookie')->getQueuedCookies())
            ->first(fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_'));

        $this->assertNotNull($cookie);
        $this->assertSame(now()->addDay()->getTimestamp(), $cookie->getExpiresTime());
    }
}
