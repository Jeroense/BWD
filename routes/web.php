<?php

Route::get('/', 'WelcomeController@login');
Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::prefix('manage')->middleware('role:superadministrator|administrator')->group(function() {
    Route::get('/', 'ManageController@index');
    Route::get('/dashboard', 'ManageController@dashboard')->name('manage.dashboard');
    Route::resource('/users', 'UserController');
    Route::resource('/system', 'SystemController');
    Route::resource('/bwdBolMapping', 'BwdBolMappingController');
    Route::get('/backup', 'ManageController@backup')->name('manage.backup');
    Route::get('/restore', 'ManageController@restore')->name('manage.restore');
    Route::resource('/permissions', 'PermissionController', ['except' => 'destroy']);
    Route::resource('/roles', 'RoleController', ['except' => 'destroy']);
    Route::resource('/metrics', 'TshirtMetricController');
    Route::get('/uploadLogo', 'SystemController@uploadLogo')->name('system.uploadLogo');
    Route::post('/storeLogo', 'SystemController@storeLogo')->name('system.storeLogo');
});

Route::prefix('products')->middleware('role:superadministrator|administrator')->group(function() {
    Route::resource('/productAttributes', 'ProductAttributeController',['except' => 'create']);
    Route::resource('/products', 'ProductController');
    Route::get('/create/{id}', 'ProductAttributeController@create')->name('productAttributes.create');
    Route::get('/download', 'ProductController@download')->name('products.download');
    Route::get('/productDownload', 'ProductController@productDownload')->name('products.productDownload');
    Route::get('/dashboard', 'ProductController@dashboard')->name('products.dashboard');
});

Route::prefix('customvariants')->middleware('role:superadministrator|administrator')->group(function() {
    Route::resource('/customvariants', 'CustomVariantController');
    Route::get('/publish','CustomVariantController@publish')->name('customvariants.publish');
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

Route::prefix('boloffers')->middleware('role:superadministrator|administrator')->group(function() {
    Route::get('/boloffers', 'BolProduktieOfferController@index')->name('boloffers.index');
    Route::get('/select-to-publish-on-bol', 'BolProduktieOfferController@select_customvariants_to_publish_on_BOL')->name('boloffers.publish.select');
    Route::post('/dump-published-on-bol', 'BolProduktieOfferController@dump_and_upload_offers_to_be_published_on_BOL')->name('boloffers.publish.dump');

    Route::get('/boloffers-pagina-2', 'BolProduktieOfferController@twee')->name('boloffers.twee');
});




Route::prefix('boltestserver')->middleware('role:superadministrator|administrator')->group(function() {
    Route::get('/boloauth', 'TestController@getBolOauth');
    Route::get('/jsonoffer-en-session-data', 'TestController@test_JSON_and_Session_Data');

    Route::get('/bolofferupload-v3', 'TestController@uploadSingleOfferToBolV3_DEMO');
    // Route::get('/bolmultipleofferupload-v3', 'TestController@uploadMultipleOffersToBolV3_DEMO');  // werkt niet bij  bol

    Route::get('/getbolorders-v3', 'TestController@getBolOrdersV3');

    Route::get('/getbolorders-v3-async', 'RefactoredTestBolOrdersAsyncController@getOrdersFromBol');  // via job/async

    Route::get('/getbolorders-v3-via-reftestorderscontroller', 'RefactoredTestBolOrdersController@getOrdersFromBol');

    Route::get('/getbolorders-v3-by-id', 'TestController@getBolOrderByIdV3');
    Route::get('/prep-offerexport-demo', 'TestController@prepare_CSV_Offer_Export_DEMO');

    Route::get('/process-status/{id}', 'TestController@getProcessStatusById' );
    Route::get('/process-statusses', 'TestController@getProcessStatusses' );

    Route::get('/test-where-first-exists', 'TestController@test_if_exists');


    Route::get('/test-jobqeue-redis-throttling', 'TestController@testJobqeueRedisThrottling');
});


Route::get('/herstelcustomvarianten', 'RefactoredTestBolOrdersController@herstel_eerder_Aangemaakte_Smake_Customvarianten_Designs_en_Composite_media_Designs');
Route::get('/maakfakeboldemocustomvarianten', 'RefactoredTestBolOrdersController@maak_Fake_Custom_Varianten_aan_voor_Test_met_Bol_Retailer_DEMO_SERVER');



Route::prefix('bolprodserver')->middleware('role:superadministrator|administrator')->group(function() {

    // volgorde van correct vullen van 'bol_produktie_offer' table is 1,2,3:
    // 1)
    Route::get('/generate-offer-export-file', 'BolProduktieOfferController@prepare_CSV_Offer_Export_PRODUCTION')->name('generate.offers.csv');
    // 2)
    Route::get('/check-offer-export-file-ready', 'BolProduktieOfferController@check_if_CSV_Offer_Export_PRODUCTION_RDY')->name('boloffer.is.csv.ready');
    // 3)
    Route::get('/get-offerexport-prod', 'BolProduktieOfferController@get_CSV_Offer_Export_PROD');



    Route::get('/process-status-by-process-status-id/{procstatusid}', 'TestController@getProcStatus_ByProcessStatusId_PRODSERVER' );


    // Route::get('/prep-offerexport-prod', 'TestController@prepare_CSV_Offer_Export_PROD');
    // Route::get('/get-offerexport-csv-prod/{offerexportid}', 'TestController@get_CSV_Offer_Export_PROD');
    // Route::get('/dump-and-db-store-dowloaded-csv-file-prod', 'TestController@dump_and_put_ProdCSVFile_in_BOL_produktie_offers_table');
    // Route::get('/process-status-by-entity-id-and-event-type', 'TestController@getProcStatus_EntId_EventType_PRODSERVER' );

    Route::get('/bolofferupload-v3', 'TestController@uploadSingleOfferToBolV3_PROD');

    Route::get('/getboloffers', 'TestController@getBolOffers');

    Route::get('/getbolorders-v3', 'TestController@getBolOrdersV3_PROD');
});
