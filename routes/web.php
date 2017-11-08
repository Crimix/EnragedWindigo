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
    return view('welcome');
});

// Authentication routes
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

// Password reset routes
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ForgotPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ForgotPasswordController@reset');

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/twitter/init', 'TwitterRequestController@init')->name('twitter.init');
Route::get('/twitter/callback', 'TwitterRequestController@callback')->name('twitter.callback');
Route::post('/twitter/confirm_key', 'TwitterRequestController@confirmKey')->name('twitter.confirmKey');
Route::get('/twitter/create', 'TwitterRequestController@create')->name('twitter.create');
Route::post('/twitter/create', 'TwitterRequestController@store')->name('twitter.store');
Route::get('/twitter/missing_auth', 'TwitterRequestController@missingAuth')->name('twitter.missingAuth');
Route::get('/twitter/{twitterRequest}', 'TwitterRequestController@done')->name('twitter.done');
