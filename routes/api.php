<?php

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'v1'], function () {
  Route::group(['namespace' => 'App\Http\Controllers\Api\V1'], function() {    
	  Route::post('/user/login', 'ApiController@login');
		Route::group(['middleware' => ['auth:api']], function () {
	     Route::get('/users', 'ApiController@users');
  	   Route::post('/user', 'ApiController@storeUser');
       Route::put('/user/{user}', 'ApiController@updateUser');
       Route::delete('/user/{user}', 'ApiController@deleteUser');
       Route::get('/logout', 'ApiController@logout');
		});
    Route::get('/country', 'ApiController@country');
    Route::get('/state/{country}', 'ApiController@state');
    Route::get('/city/{state}', 'ApiController@city');
	});
});
