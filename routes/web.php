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

//homepage->path route
Route::get('/', function () {
    return view('search');
});

Auth::routes();

//get forecast data route
Route::get('/forecast', 'WeatherController@index')->name('forecast');
