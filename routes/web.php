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

Route::get('/', function () {
    return view('auth.login');
});

Route::get('home', function () {
    return view('welcome');
});


Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');

Route::middleware('auth')->group(function (){
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin'], function () {
        Route::resource('attendance_header', 'AttendanceHeaderController', ['except' => ['show', 'edit', 'delete', 'update']]);
        Route::get('attendance_header/{user_id}/{year_month}', 'AttendanceHeaderController@show')->name('attendance_header.show');
        Route::get('attendance_header/update', 'AttendanceHeaderController@update')->name('attendance_header.update');
        Route::get('attendance_header/delete/{user_id}/{year_month}/{work_date}', 'AttendanceHeaderController@destroy')->name('attendance_header.delete');
    });
});
