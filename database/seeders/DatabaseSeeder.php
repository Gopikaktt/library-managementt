<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles and Permissions
        $this->call(RolePermissionSeeder::class);

        // 2. Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@library.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('Admin');

        // 3. Default Librarian User
        $librarian = User::firstOrCreate(
            ['email' => 'librarian@library.com'],
            [
                'name' => 'Head Librarian',
                'password' => bcrypt('password'),
            ]
        );
        $librarian->assignRole('Librarian');

        // 4. Seed Categories and Books
        $this->call(BookCategorySeeder::class);
    }
}
