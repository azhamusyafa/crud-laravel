<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root ke tasks
Route::get('/', function () {
    return redirect()->route('tasks.index');
});

// Resource routes untuk tasks
Route::resource('tasks', TaskController::class);

// DataTables server-side processing (untuk dataset besar)
Route::post('datatables/tasks', [App\Http\Controllers\DataTableController::class, 'tasksServerSide'])
     ->name('datatables.tasks');

// Additional routes untuk Ajax responses (optional, bisa menggunakan resource)
// Route::post('tasks/{task}/toggle-status', [TaskController::class, 'toggleStatus'])->name('tasks.toggle-status');