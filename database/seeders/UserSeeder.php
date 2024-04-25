<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'gepar@laposte.tg', //  myself
            'password' => Hash::make('L@poste+2024'),
            'name' => 'gepar',
            'username' => 'gepar',         
        ]);
    }
}
