<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\Message;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'display_name' => 'Administrator']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'display_name' => 'User']);
        $sellerRole = Role::firstOrCreate(['name' => 'seller', 'display_name' => 'Seller']);

        $admin = User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Admin Admin',
            'phone' => '+1-843-957-5265',
            'avatar' => 'https://via.placeholder.com/640x480.png/0077ee?text=ut',
            'is_verified' => 1,
            'password' => bcrypt('password'),
        ]);
        $admin->syncRoles([$adminRole]);

        User::factory(10)->create()->each(fn ($user) => $user->syncRoles([$userRole]));

        User::factory(5)->create()->each(fn ($seller) => $seller->syncRoles([$sellerRole]));

        Ad::factory(20)->create();
        Message::factory(30)->create();
    }
}
