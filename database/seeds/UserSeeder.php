<?php

use App\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->delete();

        $adminUser = User::create([
            'name' => 'Admin',
            'email' => 'sw709e17@cs.aau.dk',
            'password' => bcrypt('prRwPcXPHT4QqEmDtrUs'),
        ]);

        $adminUser->is_admin = true;

        $adminUser->save();
    }
}
