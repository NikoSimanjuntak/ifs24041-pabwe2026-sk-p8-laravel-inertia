<?php

use App\Features\Admin\Controllers\AdminUserController;
use App\Features\Auth\Controllers\AuthController;
use App\Features\Post\Controllers\AdminPostController;
use App\Features\Post\Controllers\PostController;
use App\Features\Post\Controllers\PublicPostController;
use App\Features\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ---------- Tingkat 1: Publik ----------
Route::get('/', [PublicPostController::class, 'index'])->name('home');
Route::get('/articles', [PublicPostController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [PublicPostController::class, 'show'])->name('articles.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ---------- Tingkat 2: Anggota (Penulis) ----------
    Route::middleware('role:member')->prefix('dashboard/posts')->name('dashboard.posts.')->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('index');
        Route::post('/', [PostController::class, 'store'])->name('store');
        Route::get('/export', [PostController::class, 'export'])->name('export');
        Route::post('/import', [PostController::class, 'import'])->name('import');
        Route::post('/ai-generate', [PostController::class, 'aiGenerate'])->name('ai-generate');
        Route::post('/ai-save', [PostController::class, 'aiSave'])->name('ai-save');

        Route::middleware('post.owner')->where(['post' => '[0-9]+'])->group(function () {
            Route::get('/{post}', [PostController::class, 'show'])->name('show');
            Route::put('/{post}', [PostController::class, 'update'])->name('update');
            Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy');
            Route::patch('/{post}/publish', [PostController::class, 'publish'])->name('publish');
            Route::post('/{post}/cover', [PostController::class, 'uploadCover'])->name('cover.store');
            Route::delete('/{post}/cover', [PostController::class, 'removeCover'])->name('cover.destroy');
        });
    });

    // ---------- Tingkat 3: Admin ----------
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
        Route::get('/users/{user}/posts', [AdminUserController::class, 'posts'])->name('users.posts');
        Route::get('/users/{user}/posts/export', [AdminUserController::class, 'export'])->name('users.posts.export');
        Route::get('/posts', [AdminPostController::class, 'index'])->name('posts');
        Route::get('/posts/export', [AdminPostController::class, 'export'])->name('posts.export');
    });
});
