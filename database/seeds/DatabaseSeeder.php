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
                'last_name' => '一般',
                'first_name' => '社員',
                'last_name_kana' => 'イッパン',
                'first_name_kana' => 'シャイン',
                'email' => 'test2@example.com',
                'password' => Hash::make('test2'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 3,
                'last_name' => '営業',
                'first_name' => '社員',
                'last_name_kana' => 'エイギョウ',
                'first_name_kana' => 'シャイン',
                'email' => 'test3@example.com',
                'password' => Hash::make('test3'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 4,
                'last_name' => '総務',
                'first_name' => '社員',
                'last_name_kana' => 'ソウム',
                'first_name_kana' => 'シャイン',
                'email' => 'test4@example.com',
                'password' => Hash::make('test4'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 5,
                'last_name' => '事業',
                'first_name' => '社員',
                'last_name_kana' => 'ジギョウ',
                'first_name_kana' => 'シャイン',
                'email' => 'test5@example.com',
                'password' => Hash::make('test5'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 6,
                'last_name' => '人事',
                'first_name' => '社員',
                'last_name_kana' => 'ジンジ',
                'first_name_kana' => 'シャイン',
                'email' => 'test6@example.com',
                'password' => Hash::make('test6'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 7,
                'last_name' => '営業',
                'first_name' => '一郎',
                'last_name_kana' => 'エイギョウ',
                'first_name_kana' => 'イチロウ',
                'email' => 'test7@example.com',
                'password' => Hash::make('test7'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 8,
                'last_name' => '営業',
                'first_name' => '二郎',
                'last_name_kana' => 'エイギョウ',
                'first_name_kana' => 'ジロウ',
                'email' => 'test8@example.com',
                'password' => Hash::make('test8'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 9,
                'last_name' => '営業',
                'first_name' => '三郎',
                'last_name_kana' => 'エイギョウ',
                'first_name_kana' => 'サブロウ',
                'email' => 'test9@example.com',
                'password' => Hash::make('test9'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 10,
                'last_name' => '営業',
                'first_name' => '四郎',
                'last_name_kana' => 'エイギョウ',
                'first_name_kana' => 'シロウ',
                'email' => 'test10@example.com',
                'password' => Hash::make('test10'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 11,
                'last_name' => '営業',
                'first_name' => '五郎',
                'last_name_kana' => 'エイギョウ',
                'first_name_kana' => 'ゴロウ',
                'email' => 'test11@example.com',
                'password' => Hash::make('test11'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 12,
                'last_name' => '総務',
                'first_name' => '一郎',
                'last_name_kana' => 'ソウム',
                'first_name_kana' => 'イチロウ',
                'email' => 'test12@example.com',
                'password' => Hash::make('test12'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 13,
                'last_name' => '総務',
                'first_name' => '二郎',
                'last_name_kana' => 'ソウム',
                'first_name_kana' => 'ジロウ',
                'email' => 'test13@example.com',
                'password' => Hash::make('test13'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 14,
                'last_name' => '総務',
                'first_name' => '三郎',
                'last_name_kana' => 'ソウム',
                'first_name_kana' => 'サブロウ',
                'email' => 'test14@example.com',
                'password' => Hash::make('test14'),
                'admin_flag' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        DB::table('departments')->insert([
            [
                'id' => 1,
                'name' => '管理部',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'name' => '営業部',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 3,
                'name' => '総務部',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 4,
                'name' => '事業部',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 5,
                'name' => '人事部',
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
            ],
            [
                'id' => 6,
                'user_id' => 7,
                'department_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 7,
                'user_id' => 8,
                'department_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 8,
                'user_id' => 9,
                'department_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 9,
                'user_id' => 10,
                'department_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 10,
                'user_id' => 11,
                'department_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 11,
                'user_id' => 12,
                'department_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 12,
                'user_id' => 13,
                'department_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 13,
                'user_id' => 14,
                'department_id' => 3,
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

        $users = DB::table('users')->pluck('id');
        $now = Carbon::now();

        foreach ($users as $userId) {
            DB::table('paid_leave_defaults')->insert([
                'user_id' => $userId,
                'default_days' => 10,
                'remaining_days' => 10,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1
            ]);
        }

        // AttendanceHeaderのテストデータ
        DB::table('attendance_header')->insert([
            'id' => 1,
            'user_id' => 2,
            'year_month' => '2025-01-01',
            'working_days' => 5,
            'overtime_hours' => '01:30:00',
            'scheduled_working_hours' => '40:00:00',
            'working_hours' => '41:30:00',
            'created_by' => 2,
            'updated_by' => 2,
            'created_at' => '2025-01-28 02:23:35',
            'updated_at' => '2025-01-28 02:24:32',
            'confirm_flag' => null
        ]);

        // AttendanceDailyのテストデータ
        DB::table('attendance_daily')->insert([
            [
                'id' => 1,
                'attendance_header_id' => 1,
                'work_date' => '2025-01-01',
                'attendance_class' => 0,
                'working_time' => '10:00:00',
                'leave_time' => '19:01:00',
                'memo' => null,
                'scheduled_working_hours' => '08:00:00',
                'overtime_hours' => '00:01:00',
                'working_hours' => '08:01:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'created_at' => '2025-01-28 02:23:35',
                'updated_at' => '2025-01-28 02:23:35'
            ],
            [
                'id' => 2,
                'attendance_header_id' => 1,
                'work_date' => '2025-01-02',
                'attendance_class' => 0,
                'working_time' => '10:00:00',
                'leave_time' => '19:14:00',
                'memo' => null,
                'scheduled_working_hours' => '08:00:00',
                'overtime_hours' => '00:14:00',
                'working_hours' => '08:14:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'created_at' => '2025-01-28 02:23:50',
                'updated_at' => '2025-01-28 02:23:50'
            ],
            [
                'id' => 3,
                'attendance_header_id' => 1,
                'work_date' => '2025-01-03',
                'attendance_class' => 0,
                'working_time' => '10:00:00',
                'leave_time' => '19:16:00',
                'memo' => null,
                'scheduled_working_hours' => '08:00:00',
                'overtime_hours' => '00:16:00',
                'working_hours' => '08:16:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'created_at' => '2025-01-28 02:24:06',
                'updated_at' => '2025-01-28 02:24:07'
            ],
            [
                'id' => 4,
                'attendance_header_id' => 1,
                'work_date' => '2025-01-04',
                'attendance_class' => 0,
                'working_time' => '10:00:00',
                'leave_time' => '19:29:00',
                'memo' => null,
                'scheduled_working_hours' => '08:00:00',
                'overtime_hours' => '00:29:00',
                'working_hours' => '08:29:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'created_at' => '2025-01-28 02:24:20',
                'updated_at' => '2025-01-28 02:24:20'
            ],
            [
                'id' => 5,
                'attendance_header_id' => 1,
                'work_date' => '2025-01-05',
                'attendance_class' => 0,
                'working_time' => '10:00:00',
                'leave_time' => '19:31:00',
                'memo' => null,
                'scheduled_working_hours' => '08:00:00',
                'overtime_hours' => '00:31:00',
                'working_hours' => '08:31:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'created_at' => '2025-01-28 02:24:32',
                'updated_at' => '2025-01-28 02:24:32'
            ]
        ]);

        // 2つ目のAttendanceHeaderのテストデータ
        DB::table('attendance_header')->insert([
            'id' => 2,
            'user_id' => 2,
            'year_month' => '2025-11-01',
            'working_days' => 1,
            'overtime_hours' => '00:00:00',
            'scheduled_working_hours' => '09:00:00',
            'working_hours' => '09:00:00',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => '2025-01-28 02:43:19',
            'updated_at' => '2025-01-28 02:43:19',
            'confirm_flag' => null
        ]);

        // 追加のAttendanceDailyのテストデータ
        DB::table('attendance_daily')->insert([
            [
                'id' => 6,
                'attendance_header_id' => 1,
                'work_date' => '2025-01-06',
                'attendance_class' => 1,
                'working_time' => '10:00:00',
                'leave_time' => '19:00:00',
                'memo' => null,
                'scheduled_working_hours' => '09:00:00',
                'overtime_hours' => '00:00:00',
                'working_hours' => '09:00:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:32:39',
                'updated_at' => '2025-01-28 02:32:39'
            ],
            [
                'id' => 7,
                'attendance_header_id' => 1,
                'work_date' => '2025-01-07',
                'attendance_class' => 1,
                'working_time' => '10:00:00',
                'leave_time' => '19:00:00',
                'memo' => null,
                'scheduled_working_hours' => '09:00:00',
                'overtime_hours' => '00:00:00',
                'working_hours' => '09:00:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:33:14',
                'updated_at' => '2025-01-28 02:33:14'
            ],
            [
                'id' => 8,
                'attendance_header_id' => 1,
                'work_date' => '2025-01-08',
                'attendance_class' => 1,
                'working_time' => '10:00:00',
                'leave_time' => '19:00:00',
                'memo' => null,
                'scheduled_working_hours' => '09:00:00',
                'overtime_hours' => '00:00:00',
                'working_hours' => '09:00:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:33:40',
                'updated_at' => '2025-01-28 02:33:40'
            ],
            [
                'id' => 9,
                'attendance_header_id' => 2,
                'work_date' => '2025-11-01',
                'attendance_class' => 1,
                'working_time' => '10:00:00',
                'leave_time' => '19:00:00',
                'memo' => null,
                'scheduled_working_hours' => '09:00:00',
                'overtime_hours' => '00:00:00',
                'working_hours' => '09:00:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:43:19',
                'updated_at' => '2025-01-28 02:43:19'
            ]
        ]);

        // BreakTimeのテストデータ
        DB::table('break_times')->insert([
            [
                'id' => 1,
                'attendance_daily_id' => 6,
                'break_time_from' => '12:00:00',
                'break_time_to' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:32:39',
                'updated_at' => '2025-01-28 02:32:39'
            ],
            [
                'id' => 2,
                'attendance_daily_id' => 7,
                'break_time_from' => '12:00:00',
                'break_time_to' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:33:14',
                'updated_at' => '2025-01-28 02:33:14'
            ],
            [
                'id' => 3,
                'attendance_daily_id' => 8,
                'break_time_from' => '12:00:00',
                'break_time_to' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:33:40',
                'updated_at' => '2025-01-28 02:33:40'
            ],
            [
                'id' => 4,
                'attendance_daily_id' => 1,
                'break_time_from' => '12:00:00',
                'break_time_to' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:34:02',
                'updated_at' => '2025-01-28 02:34:02'
            ],
            [
                'id' => 5,
                'attendance_daily_id' => 2,
                'break_time_from' => '12:00:00',
                'break_time_to' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:34:18',
                'updated_at' => '2025-01-28 02:34:18'
            ],
            [
                'id' => 6,
                'attendance_daily_id' => 3,
                'break_time_from' => '12:00:00',
                'break_time_to' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:40:41',
                'updated_at' => '2025-01-28 02:40:41'
            ],
            [
                'id' => 7,
                'attendance_daily_id' => 4,
                'break_time_from' => '12:00:00',
                'break_time_to' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:41:04',
                'updated_at' => '2025-01-28 02:41:04'
            ],
            [
                'id' => 8,
                'attendance_daily_id' => 5,
                'break_time_from' => '12:00:00',
                'break_time_to' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:41:32',
                'updated_at' => '2025-01-28 02:41:32'
            ],
            [
                'id' => 9,
                'attendance_daily_id' => 9,
                'break_time_from' => '12:00:00',
                'break_time_to' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:43:19',
                'updated_at' => '2025-01-28 02:43:19'
            ]
        ]);

        // PaidLeaveRequestのテストデータ
        DB::table('paid_leave_requests')->insert([
            [
                'id' => 1,
                'paid_leave_default_id' => 2,
                'attendance_daily_id' => 6,
                'break_time_id' => 1,
                'status' => 0,
                'request_reason' => 'teat（申請中）',
                'return_reason' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:32:39',
                'updated_at' => '2025-01-28 02:32:39'
            ],
            [
                'id' => 2,
                'paid_leave_default_id' => 2,
                'attendance_daily_id' => 7,
                'break_time_id' => 2,
                'status' => 2,
                'request_reason' => 'test（差し戻し）',
                'return_reason' => 'test（差し戻し）',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:33:14',
                'updated_at' => '2025-01-28 02:42:16'
            ],
            [
                'id' => 3,
                'paid_leave_default_id' => 2,
                'attendance_daily_id' => 8,
                'break_time_id' => 3,
                'status' => 1,
                'request_reason' => 'test（承認済み）',
                'return_reason' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 02:33:40',
                'updated_at' => '2025-01-28 02:42:32'
            ],
            [
                'id' => 4,
                'paid_leave_default_id' => 2,
                'attendance_daily_id' => 9,
                'break_time_id' => 9,
                'status' => 0,
                'request_reason' => 'test（１週間以上経過）',
                'return_reason' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2024-01-28 02:43:19',
                'updated_at' => '2024-01-28 02:43:19'
            ]
        ]);

        // ChatRoomのテストデータ
        DB::table('chat_rooms')->insert([
            [
                'id' => 1,
                'admin_id' => 1,
                'user_id' => 2,
                'created_at' => '2025-01-28 02:50:45',
                'created_by' => 1,
                'updated_at' => '2025-01-28 02:50:45',
                'updated_by' => 1
            ],
            [
                'id' => 2,
                'admin_id' => 1,
                'user_id' => 3,
                'created_at' => '2025-01-28 02:52:47',
                'created_by' => 3,
                'updated_at' => '2025-01-28 02:52:47',
                'updated_by' => 3
            ]
        ]);

        // ChatMessageのテストデータ
        DB::table('chat_messages')->insert([
            [
                'id' => 1,
                'chat_room_id' => 1,
                'user_id' => 1,
                'message' => 'こんにちは',
                'read_flag' => 1,
                'created_at' => '2025-01-28 02:51:05',
                'created_by' => 1,
                'updated_at' => '2025-01-28 02:51:29',
                'updated_by' => 1
            ],
            [
                'id' => 2,
                'chat_room_id' => 1,
                'user_id' => 2,
                'message' => 'こんにちは',
                'read_flag' => 1,
                'created_at' => '2025-01-28 02:51:38',
                'created_by' => 2,
                'updated_at' => '2025-01-28 02:51:39',
                'updated_by' => 2
            ],
            [
                'id' => 3,
                'chat_room_id' => 2,
                'user_id' => 3,
                'message' => 'test',
                'read_flag' => 0,
                'created_at' => '2025-01-28 02:52:56',
                'created_by' => 3,
                'updated_at' => '2025-01-28 02:52:56',
                'updated_by' => 3
            ]
        ]);

        // 追加社員の有給休暇デフォルト情報
        foreach (range(7, 14) as $userId) {
            DB::table('paid_leave_defaults')->insert([
                'user_id' => $userId,
                'default_days' => 10,
                'remaining_days' => 10,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_by' => 1,
                'updated_by' => 1
            ]);
        }

        // 追加のAttendanceHeaderテストデータ
        DB::table('attendance_header')->insert([
            [
                'id' => 3,
                'user_id' => 7,
                'year_month' => '2025-01-01',
                'working_days' => 1,
                'overtime_hours' => '04:00:00',
                'scheduled_working_hours' => '09:00:00',
                'working_hours' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 03:10:49',
                'updated_at' => '2025-01-28 03:11:29',
                'confirm_flag' => null
            ],
            [
                'id' => 4,
                'user_id' => 8,
                'year_month' => '2025-01-01',
                'working_days' => 1,
                'overtime_hours' => '04:00:00',
                'scheduled_working_hours' => '09:00:00',
                'working_hours' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 03:11:53',
                'updated_at' => '2025-01-28 03:11:53',
                'confirm_flag' => null
            ],
            [
                'id' => 5,
                'user_id' => 9,
                'year_month' => '2025-01-01',
                'working_days' => 1,
                'overtime_hours' => '04:00:00',
                'scheduled_working_hours' => '09:00:00',
                'working_hours' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 03:12:39',
                'updated_at' => '2025-01-28 03:12:39',
                'confirm_flag' => null
            ],
            [
                'id' => 6,
                'user_id' => 12,
                'year_month' => '2025-01-01',
                'working_days' => 1,
                'overtime_hours' => '04:00:00',
                'scheduled_working_hours' => '09:00:00',
                'working_hours' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 03:13:03',
                'updated_at' => '2025-01-28 03:13:03',
                'confirm_flag' => null
            ],
            [
                'id' => 7,
                'user_id' => 13,
                'year_month' => '2025-01-01',
                'working_days' => 1,
                'overtime_hours' => '04:00:00',
                'scheduled_working_hours' => '09:00:00',
                'working_hours' => '13:00:00',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 03:13:30',
                'updated_at' => '2025-01-28 03:13:30',
                'confirm_flag' => null
            ]
        ]);

        // 追加のAttendanceDailyテストデータ
        DB::table('attendance_daily')->insert([
            [
                'id' => 10,
                'attendance_header_id' => 3,
                'work_date' => '2025-01-01',
                'attendance_class' => 0,
                'working_time' => '10:00:00',
                'leave_time' => '23:00:00',
                'memo' => null,
                'scheduled_working_hours' => '09:00:00',
                'overtime_hours' => '04:00:00',
                'working_hours' => '13:00:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 03:10:49',
                'updated_at' => '2025-01-28 03:10:49'
            ],
            [
                'id' => 12,
                'attendance_header_id' => 4,
                'work_date' => '2025-01-01',
                'attendance_class' => 0,
                'working_time' => '10:00:00',
                'leave_time' => '23:00:00',
                'memo' => null,
                'scheduled_working_hours' => '09:00:00',
                'overtime_hours' => '04:00:00',
                'working_hours' => '13:00:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 03:11:53',
                'updated_at' => '2025-01-28 03:11:53'
            ],
            [
                'id' => 13,
                'attendance_header_id' => 5,
                'work_date' => '2025-01-01',
                'attendance_class' => 0,
                'working_time' => '10:00:00',
                'leave_time' => '23:00:00',
                'memo' => null,
                'scheduled_working_hours' => '09:00:00',
                'overtime_hours' => '04:00:00',
                'working_hours' => '13:00:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 03:12:39',
                'updated_at' => '2025-01-28 03:12:39'
            ],
            [
                'id' => 14,
                'attendance_header_id' => 6,
                'work_date' => '2025-01-01',
                'attendance_class' => 0,
                'working_time' => '10:00:00',
                'leave_time' => '23:00:00',
                'memo' => null,
                'scheduled_working_hours' => '09:00:00',
                'overtime_hours' => '04:00:00',
                'working_hours' => '13:00:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 03:13:03',
                'updated_at' => '2025-01-28 03:13:03'
            ],
            [
                'id' => 15,
                'attendance_header_id' => 7,
                'work_date' => '2025-01-01',
                'attendance_class' => 0,
                'working_time' => '10:00:00',
                'leave_time' => '23:00:00',
                'memo' => null,
                'scheduled_working_hours' => '09:00:00',
                'overtime_hours' => '04:00:00',
                'working_hours' => '13:00:00',
                'locked_by' => null,
                'locked_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-28 03:13:30',
                'updated_at' => '2025-01-28 03:13:30'
            ]
        ]);
    }
}
