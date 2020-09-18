<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Unathenticated endpoints, these require a valid recaptcha
Route::group(['middleware' => ['api', 'recaptcha']], function() {
    Route::post('/new', 'Create@store');
    Route::post('/auth', 'Auth@store');
});

// Refresh tokens only
Route::middleware(['api', 'jwt.refresh'])
    ->post('/auth/refresh', 'Refresh@update');

// Authenticated endpoints
Route::group(['middleware' => ['api', 'jwt']], function() {
    Route::get('/user/{username}', 'User@show');
    Route::put('/user/{username}', 'User@update');
});

// Global admin endpoints
Route::group(['middleware' => ['api', 'jwt', 'role:Global Administrator']], function() {
    Route::get('/users', 'User@index');
});