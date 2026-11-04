<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\SekolahController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfilSekolahController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SekolahPublikController;

// Publik
Route::get('/', [PetaController::class, 'index'])->name('peta.index');
Route::get('/data/sekolah', [PetaController::class, 'geojson'])
    ->name('peta.data');
Route::get('/sekolah/{sekolah}', [SekolahPublikController::class, 'show'])
    ->name('sekolah.show');

// Tamu
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
});

// Pengguna yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');
Route::get('/kata-sandi', [PasswordController::class, 'edit'])->name('password.edit');
Route::put('/kata-sandi', [PasswordController::class, 'update'])->name('password.update');
Route::get('/log-aktivitas', [ActivityLogController::class, 'index'])
    ->middleware('role:admin,dinas')->name('log.index');

    // Dinas: daftar, tambah, dan hapus sekolah
    Route::middleware('role:dinas')->prefix('dinas')->name('dinas.')
        ->group(function () {
            Route::resource('sekolah', SekolahController::class)
                ->only(['index', 'create', 'store', 'destroy']);
        });

    // Dinas dan Sekolah: ubah data (dibatasi Policy)
    Route::middleware('role:dinas,sekolah')->group(function () {
        Route::get('sekolah/{sekolah}/edit', [SekolahController::class, 'edit'])
            ->name('sekolah.edit');
        Route::put('sekolah/{sekolah}', [SekolahController::class, 'update'])
            ->name('sekolah.update');
        Route::get('sekolah/{sekolah}/profil',
        [ProfilSekolahController::class, 'edit'])->name('sekolah.profil.edit');
        Route::put('sekolah/{sekolah}/profil',
        [ProfilSekolahController::class, 'update'])->name('sekolah.profil.update');
    });

    // Admin: kelola pengguna
    Route::middleware('role:admin')->prefix('admin')->name('admin.')
        ->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });

});