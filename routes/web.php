<?php

use App\Http\Controllers\AffiliatesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AffiliatesController::class, 'index']);
Route::get('affiliates/find', [AffiliatesController::class, 'find']);
Route::get('affiliates/save', [AffiliatesController::class, 'save']);
