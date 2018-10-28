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

Route::get('/', 'WelcomeController@login');
Route::get('/home', 'HomeController@index')->name('home');
// Route::get('/test', 'TestController@test');

Auth::routes();

Route::prefix('manage')->middleware('role:superadministrator|administrator|editor|author|contributor')->group(function() {
    // Auth::routes();
    Route::get('/', 'ManageController@index');
    Route::get('/dashboard', 'ManageController@dashboard')->name('manage.dashboard');
    Route::resource('/users', 'UserController');
    Route::resource('/system', 'SystemController');
    Route::resource('/permissions', 'PermissionController', ['except' => 'destroy']);
    Route::resource('/roles', 'RoleController', ['except' => 'destroy']);
});

Route::prefix('products')->middleware('role:superadministrator|administrator|editor|author|contributor')->group(function() {
    Route::get('/', 'ProductController@index');
    Route::get('/uploadImage', 'ProductController@uploadImage')->name('products.uploadImage');
    Route::get('/download', 'ProductController@download')->name('products.download');
    Route::get('/productDownload', 'ProductController@productDownload')->name('products.productDownload');
    Route::get('/dashboard', 'ProductController@dashboard')->name('products.dashboard');
    Route::post('/attachImage', 'ProductController@attachImage')->name('products.attachImage');
    Route::resource('/products', 'ProductController');
});

Route::prefix('variants')->middleware('role:superadministrator|administrator|editor|author|contributor')->group(function() {
    Route::get('/', 'VariantController@index');
    Route::get('/uploadImage', 'VariantController@uploadImage')->name('variants.uploadImage');
    Route::get('/dashboard', 'VariantController@dashboard')->name('variants.dashboard');
    Route::post('/attachImage', 'VariantController@attachImage')->name('variants.attachImage');
    Route::resource('/variants', 'VariantController');
});

Route::prefix('orders')->middleware('role:superadministrator|administrator|editor|author|contributor')->group(function() {
    Route::get('/', 'OrderController@index');
    Route::get('/index', 'OrderController@index')->name('orders.index');
    Route::get('/media', 'OrderController@media')->name('orders.media');
    Route::get('/dashboard', 'OrderController@dashboard')->name('orders.dashboard');
});

Route::prefix('designs')->middleware('role:superadministrator|administrator|editor|author|contributor')->group(function() {
    Route::get('/', 'DesignController@index');
    Route::get('/dashboard', 'DesignController@dashboard')->name('designs.dashboard');
    Route::get('/index', 'DesignController@index')->name('designs.index');
    Route::get('/upload', 'DesignController@upload')->name('designs.upload');
    Route::post('/store', 'DesignController@store')->name('designs.store');
});

Route::prefix('summaries')->middleware('role:superadministrator|administrator|editor|author|contributor')->group(function() {

});

