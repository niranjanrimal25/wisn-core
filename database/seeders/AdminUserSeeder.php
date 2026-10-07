<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Demo credentials only. Change this password before any real deployment.
        $user = User::firstOrNew(['email' => 'admin@wisn.org']);
        $user->name = 'Admin Wisn';
        $user->password = Hash::make('password');
        $user->email_verified_at = now();
        $user->is_admin = true;
        $user->save();
    }
}
