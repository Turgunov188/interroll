<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Fresh install: catalog, starting content and one account per role.
 * Every seeder here is safe to run again — nothing is duplicated or overwritten.
 *
 *   php artisan db:seed --force
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            ContentSeeder::class,
            StaffSeeder::class,
        ]);
    }
}
