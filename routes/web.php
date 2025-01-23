<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('home', function () {
    return view('welcome');
});

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');

Route::middleware('auth')->group(function () {
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');

    Route::group(['middleware' => ['auth', 'can:admin']], function () {
        Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin'], function () {
            // Attendance Header Routes
            Route::resource('attendance_header', 'AttendanceHeaderController', ['except' => ['show', 'edit', 'delete', 'update']]);
            Route::get('attendance_header/{user_id}/{year_month}', 'AttendanceHeaderController@show')->name('attendance_header.show');
            Route::post('attendance_header/update', 'AttendanceHeaderController@update')->name('attendance_header.update');
            Route::delete('attendance_header/delete/{user_id}/{year_month}/{work_date}', 'AttendanceHeaderController@destroy')->name('attendance_header.delete');
            Route::get('attendance_header/ajax_get_attendance_info', 'AttendanceHeaderController@ajaxGetAttendanceInfo')->name('attendance_header.ajax_get_attendance_info');
            Route::post('attendance_header/confirm/{user_id}/{year_month}', 'AttendanceHeaderController@confirm')->name('attendance_header.confirm');
            Route::get('attendance_header/get-request', 'AttendanceHeaderController@getRequest')->name('attendance_header.get_request');
            Route::post('attendance_header/reapply', 'AttendanceHeaderController@reapply')->name('attendance_header.reapply');

            // Department Routes
            Route::resource('department', 'DepartmentController', ['except' => ['show', 'edit', 'create', 'update', 'delete']]);
            Route::post('department/update/{department}', 'DepartmentController@update')->name('department.update');
            Route::get('department/delete/{department}', 'DepartmentController@destroy')->name('department.delete');
            Route::get('department/ajax_get_department_info', 'DepartmentController@ajaxGetDepartmentInfo')->name('department.ajax_get_department_info');

            // 部門別残業時間超過人数Excel出力
            Route::get('department/overtime-report/export', 'DepartmentController@exportOvertimeReport')->name('department.overtime_report.export');

            // Users Routes
            Route::get('users', 'UsersController@index')->name('users.index');
            Route::get('users/ajax_get_user_info', 'UsersController@ajaxGetUserInfo')->name('users.ajax_get_user_info');
            Route::post('users/update', 'UsersController@update')->name('users.update');
            Route::post('users/destroy', 'UsersController@destroy')->name('users.destroy');

            // Settings Routes
            Route::get('settings', 'SettingsController@edit')->name('settings.edit');
            Route::post('settings/update', 'SettingsController@update')->name('settings.update');

            // 有給休暇申請関連のルート
            Route::get('request', 'RequestController@index')->name('request.index');
            Route::post('request/{id}/approve', 'RequestController@approve')->name('request.approve');
            Route::post('request/{id}/return', 'RequestController@return')->name('request.return');

            // チャット機能用のルート
            Route::get('chat', 'ChatListController@index')->name('chat.list');
            Route::post('chat/create', 'ChatListController@createRoom')->name('chat.create');
            Route::get('chat/{room_id}', 'ChatRoomController@show')->name('chat.room');
            Route::post('chat/{room_id}/send', 'ChatRoomController@sendMessage')->name('chat.send');
            Route::get('chat/{room_id}/check-new-messages', 'ChatRoomController@checkNewMessages')->name('chat.check_new_messages');
        });
        Route::group(['prefix' => 'admin/attendance_daily', 'as' => 'attendance_daily.'], function () {
            Route::get('check-lock', 'Admin\AttendanceHeaderController@checkLock');
            Route::post('lock', 'Admin\AttendanceHeaderController@lock');
            Route::post('unlock', 'Admin\AttendanceHeaderController@unlock');
        });
    });

    Route::group(['middleware' => ['auth'], 'prefix' => 'user', 'as' => 'user.', 'namespace' => 'User'], function () {
        Route::group(['middleware' => ['loginUserCheck']], function () {
            // Attendance Header Routes for Users
            Route::get('attendance_header/{user_id}/{year_month}', 'AttendanceHeaderController@show')->name('attendance_header.show');
            Route::post('attendance_header/update', 'AttendanceHeaderController@update')->name('attendance_header.update');
            Route::delete('attendance_header/delete/{user_id}/{year_month}/{work_date}', 'AttendanceHeaderController@destroy')->name('attendance_header.delete');
            Route::get('attendance_header/get-request', 'AttendanceHeaderController@getRequest')->name('attendance_header.get_request');
        });

        Route::get('attendance_header/ajax_get_attendance_info', 'AttendanceHeaderController@ajaxGetAttendanceInfo')->name('attendance_header.ajax_get_attendance_info');
        Route::post('attendance_header/reapply', 'AttendanceHeaderController@reapply')->name('attendance_header.reapply');

        // チャット機能用のルート
        Route::get('chat', 'ChatListController@index')->name('chat.list');
        Route::get('chat/{room_id}', 'ChatRoomController@show')->name('chat.room');
        Route::post('chat/{room_id}/send', 'ChatRoomController@sendMessage')->name('chat.send');
        Route::get('chat/{room_id}/check-new-messages', 'ChatRoomController@checkNewMessages')->name('chat.check_new_messages');
    });
    Route::group(['prefix' => 'user/attendance_daily', 'as' => 'user.attendance_daily.', 'namespace' => 'User'], function () {
        // ロック関連のルート
        Route::get('check-lock', 'AttendanceHeaderController@checkLock')->name('check_lock');
        Route::post('lock', 'AttendanceHeaderController@lock')->name('lock');
        Route::post('unlock', 'AttendanceHeaderController@unlock')->name('unlock');
    });

    // 打刻関連のルート
    Route::group(['middleware' => ['auth', 'loginUserCheck']], function () {
        Route::get('/user/stamp/{user_id}/{year_month}', 'User\AttendanceStampController@index')->name('stamp.index');
        Route::post('/api/attendance/start', 'User\AttendanceStampController@startWork');
        Route::post('/api/attendance/end', 'User\AttendanceStampController@endWork');
        Route::post('/api/break/start', 'User\AttendanceStampController@startBreak');
        Route::post('/api/break/end', 'User\AttendanceStampController@endBreak');
    });

    Route::post('/user/chat/create', [App\Http\Controllers\User\ChatListController::class, 'createRoom'])->name('user.chat.create');

    Route::get('/api/check-admin-flag', 'Api\AdminFlagController@check');
});
