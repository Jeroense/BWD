<?php

Route::get('/', 'WelcomeController@login');
Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::prefix('manage')->middleware('role:superadministrator|administrator')->group(function() {
    Route::get('/', 'ManageController@index');
    Route::get('/dashboard', 'ManageController@dashboard')->name('manage.dashboard');
    Route::resource('/users', 'UserController');
    Route::resource('/system', 'SystemController');
    Route::get('/backup', 'ManageController@backup')->name('manage.backup');
    Route::get('/restore', 'ManageController@restore')->name('manage.restore');
    Route::resource('/permissions', 'PermissionController', ['except' => 'destroy']);
    Route::resource('/roles', 'RoleController', ['except' => 'destroy']);
    Route::resource('/metrics', 'TshirtMetricController');
});

Route::prefix('products')->middleware('role:superadministrator|administrator')->group(function() {
    Route::resource('/products', 'ProductController');
    Route::get('/download', 'ProductController@download')->name('products.download');
    Route::get('/productDownload', 'ProductController@productDownload')->name('products.productDownload');
    Route::get('/dashboard', 'ProductController@dashboard')->name('products.dashboard');
});

Route::prefix('customvariants')->middleware('role:superadministrator|administrator')->group(function() {
    Route::resource('/customvariants', 'CustomVariantController');
    Route::post('/orderVariant/{id}','CustomVariantController@orderVariant')->name('customvariants.orderVariant');
    Route::get('/orderVariant/{id}','CustomVariantController@orderVariant')->name('customvariants.orderVariant');
});

Route::post('/createVariant','CustomVariantController@createVariant')->name('customVariants.createVariant');


Route::prefix('variants')->middleware('role:superadministrator|administrator')->group(function() {
    Route::resource('/variants', 'VariantController');
    Route::get('/dashboard', 'VariantController@dashboard')->name('variants.dashboard');
    Route::get('/selectSizes/{id}', 'VariantController@selectSizes')->name('variants.selectSizes');
});

Route::prefix('orders')->middleware('role:superadministrator|administrator')->group(function() {
    Route::get('/', 'OrderController@index');
    Route::get('/create/{id}', 'OrderController@create')->name('orders.create');
    Route::post('checkoutOrder/{id}', 'OrderController@checkoutOrder')->name('checkoutOrder');
    Route::get('/index', 'OrderController@index')->name('orders.index');
    Route::get('/media', 'OrderController@media')->name('orders.media');
    Route::get('/dashboard', 'OrderController@dashboard')->name('orders.dashboard');
});

Route::prefix('designs')->middleware('role:superadministrator|administrator')->group(function() {
    Route::get('/', 'DesignController@index');
    Route::get('/dashboard', 'DesignController@dashboard')->name('designs.dashboard');
    Route::get('/index', 'DesignController@index')->name('designs.index');
    Route::get('/upload', 'DesignController@upload')->name('designs.upload');
    Route::post('/store', 'DesignController@store')->name('designs.store');
    Route::delete('/destroy/{id}', 'DesignController@destroy')->name('designs.destroy');
});

Route::prefix('summaries')->middleware('role:superadministrator|administrator')->group(function() {

});

Route::prefix('customers')->middleware('role:superadministrator|administrator')->group(function() {
    Route::resource('/customers', 'CustomerController');
});

