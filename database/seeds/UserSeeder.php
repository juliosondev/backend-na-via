<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data_time = new DateTime;
        $user = DB::table('users')->insertGetId([
            'level' => 0,
            'email' => 'root@gmail.com',
            'password' => Hash::make('123456'),
            'status' => 'activo',
            'created_at' => $data_time,
            'updated_at' => $data_time,
        ]);

        $dados_user = DB::table('users_dados')->insert([
            'user_id' => $user,
            'nome' => 'AVK JS',
            'genero' => 'm',
            'data_nascimento' => null,
            'tel' => null,
            'morada' => null,
            'foto' => null,
            'created_at' => $data_time,
            'updated_at' => $data_time,
        ]);
    }
}
