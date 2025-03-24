<?php

use App\Http\Controllers\Api\CommentsController;
use App\Http\Controllers\Api\PostsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CaptchaController;

// posts
Route::get('/posts', [PostsController::class, 'index']);
Route::get('/posts/{post}', [PostsController::class, 'show']);

//comments
Route::get('/posts/{post}/comments', [CommentsController::class, 'index']);
Route::post('/posts/{post}/comments', [CommentsController::class, 'store']);
Route::post('/comments/{comment}/get-children', [CommentsController::class, 'getChildren']);
Route::post('/comments/{comment}/reply', [CommentsController::class, 'reply']);