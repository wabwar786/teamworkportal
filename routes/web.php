<?php

use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\ChatController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\DeviceController;
use App\Http\Controllers\Portal\EmployeeController;
use App\Http\Controllers\Portal\MemberController;
use App\Http\Controllers\Portal\MessageController;
use App\Http\Controllers\Portal\OwnerController;
use App\Http\Controllers\Portal\SearchController;
use App\Http\Controllers\Portal\SupportController;
use App\Http\Controllers\Portal\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Member side — NO login. Employee picks their name.
|--------------------------------------------------------------------------
*/
Route::get('/', [MemberController::class, 'start'])->name('member.start');
Route::post('/pick', [MemberController::class, 'pick'])->name('member.pick');
Route::get('/my-day', [MemberController::class, 'day'])->name('member.day');
Route::post('/my-day/save', [MemberController::class, 'save'])->name('member.save');
Route::post('/my-day/task/{task}/done', [MemberController::class, 'completeTask'])->name('member.task.done');
Route::post('/leave', [MemberController::class, 'leave'])->name('member.leave');

// Member team chat (session-based)
Route::get('/my-day/chat/contacts', [ChatController::class, 'contacts'])->name('member.chat.contacts');
Route::get('/my-day/chat/thread/{kind}/{id}', [ChatController::class, 'thread'])->name('member.chat.thread')->whereIn('kind', ['owner', 'emp']);
Route::post('/my-day/chat/send', [ChatController::class, 'send'])->name('member.chat.send');
Route::get('/my-day/chat/poll', [ChatController::class, 'poll'])->name('member.chat.poll');

/*
|--------------------------------------------------------------------------
| Owner / head login
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest:web');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest:web');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Portal — owners and heads (auth required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:web')->prefix('portal')->name('portal.')->group(function () {
    // Workspace
    Route::get('/today', [DashboardController::class, 'today'])->name('today');
    Route::get('/analyze', [DashboardController::class, 'analyze'])->name('analyze');
    Route::get('/full-log', [DashboardController::class, 'fullLog'])->name('log');
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Tasks + messaging
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/message', [TaskController::class, 'message'])->name('tasks.message');

    // Vault
    Route::get('/vault', [DashboardController::class, 'fullLog'])->name('vault'); // reuse; view filters saved
    Route::view('/vault-view', 'portal.vault')->name('vault.view');

    // Devices
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices');
    Route::get('/devices/activity', [DeviceController::class, 'activity'])->name('devices.activity');
    Route::get('/devices/files', [DeviceController::class, 'files'])->name('devices.files');
    Route::get('/devices/blocklist', [DeviceController::class, 'blocklist'])->name('devices.blocklist');
    Route::post('/devices/blocklist', [DeviceController::class, 'addBlock'])->name('devices.block.add');
    Route::delete('/devices/blocklist/{rule}', [DeviceController::class, 'removeBlock'])->name('devices.block.remove');
    Route::get('/devices/remote', [DeviceController::class, 'remote'])->name('devices.remote');
    Route::post('/devices/remote', [DeviceController::class, 'runCommand'])->name('devices.remote.run');

    // Support
    Route::get('/support', [SupportController::class, 'inbox'])->name('support');
    Route::post('/support/{conversation}/reply', [SupportController::class, 'reply'])->name('support.reply');
    Route::get('/scripts', [SupportController::class, 'scripts'])->name('scripts');
    Route::post('/scripts/client', [SupportController::class, 'addClient'])->name('scripts.client');

    // Team messages (owner/head <-> member and owner <-> owner)
    Route::get('/messages', [MessageController::class, 'index'])->name('messages');
    Route::get('/messages/contacts', [MessageController::class, 'contacts'])->name('messages.contacts');
    Route::get('/messages/thread/{kind}/{id}', [MessageController::class, 'thread'])->name('messages.thread')->whereIn('kind', ['owner', 'emp']);
    Route::post('/messages/send', [MessageController::class, 'send'])->name('messages.send');
    Route::get('/messages/poll', [MessageController::class, 'poll'])->name('messages.poll');

    // Employees (add / edit / archive-delete / restore)
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::post('/employees/{employee}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::get('/employees/{employee}/log', [EmployeeController::class, 'archivedLog'])->name('employees.log');

    // Owners & heads (super only)
    Route::middleware('tier:super')->group(function () {
        Route::get('/messages/all', [MessageController::class, 'allMessages'])->name('messages.all');
        Route::get('/owners', [OwnerController::class, 'index'])->name('owners');
        Route::post('/owners', [OwnerController::class, 'store'])->name('owners.store');
        Route::put('/owners/{owner}', [OwnerController::class, 'update'])->name('owners.update');
        Route::delete('/owners/{owner}', [OwnerController::class, 'destroy'])->name('owners.destroy');
    });
});
