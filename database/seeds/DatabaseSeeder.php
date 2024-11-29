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
                'last_name' => '管理者',
                'first_name' => '太郎',
                'last_name_kana' => 'カンリシャ',
                'first_name_kana' => 'タロウ',
                'email' => 'test1@example.com',
                'password' => Hash::make('test1'),
                'admin_flag' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'last_name' => '一般社員',
                'first_name' => '太郎',
                'last_name_kana' => 'イッパンシャイン',
                'first_name_kana' => 'タロウ',
                'email' => 'test2@example.com',
                'password' => Hash::make('test2'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 3,
                'last_name' => '営業',
                'first_name' => '太郎',
                'last_name_kana' => 'エイギョウ',
                'first_name_kana' => 'タロウ',
                'email' => 'test3@example.com',
                'password' => Hash::make('test3'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 4,
                'last_name' => '総務',
                'first_name' => '太郎',
                'last_name_kana' => 'ソウム',
                'first_name_kana' => 'タロウ',
                'email' => 'test4@example.com',
                'password' => Hash::make('test4'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 5,
                'last_name' => '事業',
                'first_name' => '太郎',
                'last_name_kana' => 'ジギョウ',
                'first_name_kana' => 'タロウ',
                'email' => 'test5@example.com',
                'password' => Hash::make('test5'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 6,
                'last_name' => '人事',
                'first_name' => '太郎',
                'last_name_kana' => 'ジンジ',
                'first_name_kana' => 'タロウ',
                'email' => 'test6@example.com',
                'password' => Hash::make('test6'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        DB::table('departments')->insert([
            [
                'id' => 1,
                'name' => '管理',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'name' => '営業',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 3,
                'name' => '総務',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 4,
                'name' => '事業',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 5,
                'name' => '人事',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        DB::table('department_members')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'department_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'user_id' => 3,
                'department_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 3,
                'user_id' => 4,
                'department_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 4,
                'user_id' => 5,
                'department_id' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 5,
                'user_id' => 6,
                'department_id' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        DB::table('company')->insert([
            [
                'id' => 1,
                'base_time_from' => new Carbon('10:00'),
                'base_time_to' => new Carbon('19:00'),
                'time_fraction' => 1,
                'rounding_scope' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
    }
}
