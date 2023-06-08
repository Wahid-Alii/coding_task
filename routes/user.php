<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('get-suggestions', [HomeController::class, 'getSuggestions']);
Route::get('get-request', [HomeController::class, 'getRequest']);

Route::get('get-connections', [HomeController::class, 'getConnections']);
Route::get('get-connections-in-common', [HomeController::class, 'getConnectionsInCommon']);
Route::post('send-connection-request', [HomeController::class, 'sendConnectionRequest']);
Route::post('delete-connection-request', [HomeController::class, 'deleteConnectionRequest']);
Route::post('accept-connection-request', [HomeController::class, 'acceptConnectionRequest']);
Route::post('remove-connection-request', [HomeController::class, 'removeConnectionRequest']);