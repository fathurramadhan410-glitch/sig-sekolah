<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\SekolahController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfilSekolahController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SekolahPublikController;


// Publik
Route::controller(PetaController::class)->group(function () {
    Route::get('/', 'index')->name('peta.index');
    Route::get('/pencarian', 'cari')->name('peta.cari');
    Route::get('/data/sekolah', 'geojson')->name('peta.data');
    Route::get('/klaster', 'klaster')->name('peta.klaster');
    Route::get('/peta-panas', 'panas')->name('peta.panas');
    Route::get('/wilayah', 'wilayah')->name('peta.wilayah');
    Route::get('/data/wilayah', 'geojsonWilayah')->name('peta.data.wilayah');
    Route::get('/rute', 'rute')->name('peta.rute');
    Route::get('/alamat', 'alamat')->name('peta.alamat');

});

Route::get('/health', function () {
    $start = microtime(true);

    try {
        DB::connection()->getPdo();
        $totalSekolah = DB::table('sekolahs')->count();
        $latencyMs = round((microtime(true) - $start) * 1000, 2);

        return response()->json([
            'status' => 'healthy',
            'app' => config('app.name'),
            'database' => 'connected',
            'db_latency_ms' => $latencyMs,
            'total_sekolah' => $totalSekolah,
            'timestamp' => now()->toIso8601String(),
        ], 200);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'unhealthy',
            'database' => 'disconnected',
            'error' => $e->getMessage(),
        ], 503);
    }
})->name('health');

Route::get('/data/sekolah/sekitar', [PetaController::class, 'sekitar'])->name('sekolah.sekitar');

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
    Route::get('/kata-sandi', [PasswordController::class, 'edit'])
        ->name('password.edit');
    Route::put('/kata-sandi', [PasswordController::class, 'update'])
        ->name('password.update');
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
        Route::get('sekolah/{sekolah}/profil', [ProfilSekolahController::class, 'edit'])
            ->name('sekolah.profil.edit');
        Route::put('sekolah/{sekolah}/profil', [ProfilSekolahController::class, 'update'])
            ->name('sekolah.profil.update');
    });

    // Admin: kelola pengguna
    Route::middleware('role:admin')->prefix('admin')->name('admin.')
        ->group(function () {
            Route::resource('users', UserController::class)->except('show');
        });
});