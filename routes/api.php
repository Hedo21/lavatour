<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware(['auth:sanctum', 'inactivity.timeout'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/users/{id}', function (Request $request, $id) {
        return $request->user();
    });
    Route::get('/profile', [UserController::class, 'getprofile']);
    Route::put('/profile', [UserController::class, 'update']);
    Route::post('/logout', [UserController::class, 'logout']);
});
