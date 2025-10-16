<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        if (!Usuario::where('username', 'admin')->exists()) {
            Usuario::create([
                'username' => 'admin',
                'password' => 'admin'
            ]);
        }
    }
}
