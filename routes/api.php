<?php

use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ProtocolController;
use App\Http\Controllers\Api\ReindexController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ThreadController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Support\Facades\Route;

Route::post('reindex', ReindexController::class);

Route::apiResource('protocols', ProtocolController::class);
Route::apiResource('threads', ThreadController::class);

Route::get('threads/{id}/comments', [CommentController::class, 'index']);
Route::post('threads/{id}/comments', [CommentController::class, 'storeForThread']);
Route::post('comments', [CommentController::class, 'store']);

Route::get('protocols/{id}/reviews', [ReviewController::class, 'index']);
Route::post('reviews', [ReviewController::class, 'store']);

Route::post('votes', [VoteController::class, 'store']);
