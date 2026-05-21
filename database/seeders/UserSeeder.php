<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //初期管理者を作成
        User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
