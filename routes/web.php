<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


/*
|--------------------------------------------------------------------------
| Authenticated & Verified Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    
Route::get('dashboard', function () {
    $kmlFiles = [];
    foreach (File::files(public_path('kml')) as $file) {
        if ($file->getExtension() === 'kml') {
            $kmlFiles[] = asset('kml/' . $file->getFilename());
        }
    }
    return Inertia::render('Dashboard', [
        'kmlUrls' => $kmlFiles,
    ]);
})->name('dashboard');
    Route::get('road_monitoring', function () {
        return Inertia::render('RoadMonitoring', [
            'landuseKmlUrl' => asset('kml/landuse_KML.kml'),
            'roadsKmlUrl' => asset('kml/TNDG_ROADNETWORKS_KML.kml'),
        ]);
    })->name('road_monitoring');
    Route::get('vehicle_analytics', function () {
        return Inertia::render('VehicleAnalytics',
     [
            'landuseKmlUrl' => asset('kml/landuse_KML.kml'),
            'roadsKmlUrl' => asset('kml/TNDG_ROADNETWORKS_KML.kml'),
        ]);
    })->name('vehicle_analytics');
    Route::get('map_view', function () {
        return Inertia::render('MapView',
     [
            'landuseKmlUrl' => asset('kml/landuse_KML.kml'),
            'roadsKmlUrl' => asset('kml/TNDG_ROADNETWORKS_KML.kml'),
        ]);
    })->name('map_view');
    Route::get('maintenance_alert', function () {
        return Inertia::render('MaintenanceAlert');
    })->name('maintenance_alert');

    Route::get('historical_data', function () {
        return Inertia::render('HistoricalData');
    })->name('historical_data');
    Route::get('users', function () {
        return Inertia::render('UserManagement');
    })->name('users');
    Route::get('backup_and_restore', function () {
        return Inertia::render('BackupAndRestore');
    })->name('backup_and_restore');

});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
