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

Route::get('/', function() {
    return redirect('/sites');
});

Route::get('/sites', 'SitesController@index');
Route::post('/sites', 'SitesController@store');

Route::post('/sites/{id}', 'SitesController@storeTrackingData')
    ->middleware('cors');

Route::get('/sites/{id}/snippet', 'SitesController@getSnippet');
Route::get('/sites/{id}/tracking-code', 'SitesController@getTrackingCode');

Route::get('/mailchimp/lists', 'MailchimpController@getLists');
Route::post('/mailchimp/subscribe', 'MailchimpController@subscribe');
