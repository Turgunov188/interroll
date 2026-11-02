<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * One working account per role, so the admin panel can be entered right after deploy.
 *
 *   php artisan db:seed --class=StaffSeeder --force
 *
 * Emails come from .env (STAFF_OWNER_EMAIL, STAFF_ADMIN_EMAIL, STAFF_SALES_EMAIL) with
 * owner@ / admin@ / sales@ <APP_URL host> as the fallback. Each new account gets a random
 * password that is printed once; change it after the first login. Accounts that already
 * exist are never touched, so running the seeder again cannot reset anyone's password.
 */
class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $created = [];

        foreach (UserRole::cases() as $role) {
            $email = $this->email($role);

            if (User::query()->where('email', $email)->exists()) {
                $this->command?->line("  {$role->value}: {$email} already exists, skipped");

                continue;
            }

            $password = Str::password(16, symbols: false);

            User::query()->create([
                'name' => $role->getLabel(),
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'is_active' => true,
            ]);

            $created[] = [$role->getLabel(), $email, $password];
        }

        if ($created !== [] && $this->command !== null) {
            $this->command->warn('  Save these passwords now and change them after the first login:');
            $this->command->table(['Role', 'Email', 'Password'], $created);
        }
    }

    private function email(UserRole $role): string
    {
        $configured = env('STAFF_'.strtoupper($role->value).'_EMAIL');

        if (is_string($configured) && $configured !== '') {
            return Str::lower(trim($configured));
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'interroll.uz';

        return $role->value.'@'.Str::after($host, 'www.');
    }
}
