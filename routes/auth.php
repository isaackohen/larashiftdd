<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\Social\DiscordController;
use App\Http\Controllers\Auth\Social\FacebookController;
use App\Http\Controllers\Auth\Social\GoogleController;
use App\Http\Controllers\Auth\Social\SteamController;
use App\Http\Controllers\Auth\Social\VkController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->post('/update', 'LoginController@update');
Route::post('resetPassword', 'LoginController@resetPassword');
Route::post('/login', 'LoginController@login');
Route::post('/demoLogin', 'LoginController@demologin');
Route::post('/register', 'RegisterController@register');

Route::get('/vk', 'VkController@vk');
Route::get('/discord', 'DiscordController@discord');
Route::get('/steam', 'SteamController@steam');
Route::get('/fb', 'FacebookController@facebook');
Route::get('/google', 'GoogleController@google');

Route::get('/logout', 'LogoutController@logout');