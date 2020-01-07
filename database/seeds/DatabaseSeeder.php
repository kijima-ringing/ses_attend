<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        DB::table('users')->insert([
            [
                'id' => 1,
                'last_name' => '山田',
                'first_name' => '太郎',
                'last_name_kana' => 'ヤマダ',
                'first_name_kana' => 'タロウ',
                'email' => 'test001@example.com',
                'password' => Hash::make('test123+'),
                'admin_flag' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        DB::table('company')->insert([
            [
                'id' => 1,
                'base_time_from' => new Carbon('10:00:00'),
                'base_time_to' => new Carbon('19:00:00'),
                'time_fraction' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
    }
}
