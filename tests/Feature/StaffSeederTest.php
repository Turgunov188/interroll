<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\StaffSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_active_account_is_created_per_role(): void
    {
        config(['app.url' => 'https://www.interroll.uz']);

        $this->seed(StaffSeeder::class);

        foreach (UserRole::cases() as $role) {
            $user = User::query()->where('email', $role->value.'@interroll.uz')->sole();

            $this->assertSame($role, $user->role);
            $this->assertTrue($user->is_active);
        }
        $this->assertSame(count(UserRole::cases()), User::query()->count());
    }

    public function test_running_again_never_resets_an_existing_password(): void
    {
        config(['app.url' => 'https://interroll.uz']);
        $owner = User::factory()->create([
            'email' => 'owner@interroll.uz',
            'password' => 'kept-secret',
            'role' => UserRole::Owner,
        ]);

        $this->seed(StaffSeeder::class);
        $this->seed(StaffSeeder::class);

        $this->assertTrue(Hash::check('kept-secret', $owner->fresh()->password));
        $this->assertSame(count(UserRole::cases()), User::query()->count());
    }
}
