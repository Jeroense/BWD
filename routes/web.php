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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'WelcomeController@login');
Route::get('/test', 'TestController@test');

Auth::routes();

Route::prefix('manage')->middleware('role:superadministrator|administrator|editor|author|contributor')->group(function() {
    Route::get('/', 'ManageController@index');
    Route::get('/dashboard', 'ManageController@dashboard')->name('manage.dashboard');
    Route::resource('/users', 'UserController');
    Route::resource('/system', 'SystemController');
    Route::resource('/permissions', 'PermissionController', ['except' => 'destroy']);
    Route::resource('/roles', 'RoleController', ['except' => 'destroy']);
});

Route::prefix('variants')->middleware('role:superadministrator|administrator|editor|author|contributor')->group(function() {
    Route::get('/', 'VariantController@index');
    Route::get('/index', 'VariantController@index')->name('variants.index');
    Route::get('/media', 'VariantController@media')->name('variants.media');
    Route::get('/dashboard', 'VariantController@dashboard')->name('variants.dashboard');
});

Route::prefix('orders')->middleware('role:superadministrator|administrator|editor|author|contributor')->group(function() {
    Route::get('/', 'OrderController@index');
    Route::get('/index', 'OrderController@index')->name('orders.index');
    Route::get('/media', 'OrderController@media')->name('orders.media');
    Route::get('/dashboard', 'OrderController@dashboard')->name('orders.dashboard');
});

Route::prefix('design')->middleware('role:superadministrator|administrator|editor|author|contributor')->group(function() {
    Route::get('/', 'DesignController@index');
    Route::get('/dashboard', 'DesignController@dashboard')->name('design.dashboard');
    Route::get('/index', 'DesignController@index')->name('design.index');
    Route::get('/upload', 'DesignController@upload')->name('design.upload');
    Route::post('/fSave', 'DesignController@store')->name('design.fSave');

});

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/test', 'TestController@test')->name('test');
