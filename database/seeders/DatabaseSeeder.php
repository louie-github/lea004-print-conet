<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Price;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'username' => 'admin',
            'firstname' => 'Admin',
            'lastname' => 'Admin',
            'email' => 'admin@test.com',
            'is_admin' => true,
            'password' => bcrypt('secret')
        ]);

        // Manually create a test user
        DB::table('users')->insert([
            'username' => 'test',
            'firstname' => 'Test',
            'lastname' => 'Person',
            'email' => 'test@test.com',
            'is_admin' => false,
            'password' => bcrypt('password')
        ]);

        // Document::factory()->count(10)->create();

        Price::factory()->create();
    }
}
