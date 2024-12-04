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
            Route::get('attendance_header/delete/{user_id}/{year_month}/{work_date}', 'AttendanceHeaderController@destroy')->name('attendance_header.delete');
            Route::get('attendance_header/ajax_get_attendance_info', 'AttendanceHeaderController@ajaxGetAttendanceInfo')->name('attendance_header.ajax_get_attendance_info');
            Route::post('attendance_header/confirm/{user_id}/{year_month}', 'AttendanceHeaderController@confirm')->name('attendance_header.confirm');

            // Department Routes
            Route::resource('department', 'DepartmentController', ['except' => ['show', 'edit', 'create', 'update', 'delete']]);
            Route::post('department/update/{department}', 'DepartmentController@update')->name('department.update');
            Route::get('department/delete/{department}', 'DepartmentController@destroy')->name('department.delete');
            Route::get('department/ajax_get_department_info', 'DepartmentController@ajaxGetDepartmentInfo')->name('department.ajax_get_department_info');

            // 部門別残業時間超過人数のエクセル出力
            Route::get('department/overtime-report/export', 'DepartmentController@exportOvertimeReport')->name('department.overtime_report.export');

            // Users Routes
            Route::get('users', 'UsersController@index')->name('users.index');
            Route::get('users/ajax_get_user_info', 'UsersController@ajaxGetUserInfo')->name('users.ajax_get_user_info');
            Route::post('users/update', 'UsersController@update')->name('users.update');
            Route::post('users/destroy', 'UsersController@destroy')->name('users.destroy');

            // Settings Routes
            Route::get('settings', 'SettingsController@edit')->name('settings.edit');
            Route::post('settings/update', 'SettingsController@update')->name('settings.update');
        });
        Route::group(['prefix' => 'admin/attendance_daily', 'as' => 'attendance_daily.'], function () {
            Route::get('check-lock', 'Admin\AttendanceHeaderController@checkLock');
            Route::post('lock', 'Admin\AttendanceHeaderController@lock');
            Route::post('unlock', 'Admin\AttendanceHeaderController@unlock');
        });
    });

    Route::group(['prefix' => 'user', 'as' => 'user.', 'namespace' => 'User'], function () {
        Route::group(['middleware' => ['loginUserCheck']], function () {
            // Attendance Header Routes for Users
            Route::get('attendance_header/{user_id}/{year_month}', 'AttendanceHeaderController@show')->name('attendance_header.show');
            Route::post('attendance_header/update', 'AttendanceHeaderController@update')->name('attendance_header.update');
            Route::get('attendance_header/delete/{user_id}/{year_month}/{work_date}', 'AttendanceHeaderController@destroy')->name('attendance_header.delete');
        });

        Route::get('attendance_header/ajax_get_attendance_info', 'AttendanceHeaderController@ajaxGetAttendanceInfo')->name('attendance_header.ajax_get_attendance_info');
    });
});
