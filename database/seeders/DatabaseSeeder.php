<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Fresh install: catalog (with the parts from the catalogue PDF), starting content and one account per role.
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
            CatalogPdfSeeder::class,
            ContentSeeder::class,
            StaffSeeder::class,
        ]);
    }
}
