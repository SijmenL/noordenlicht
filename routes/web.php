<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NonLoggedInController;
use Illuminate\Support\Facades\Route;

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

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/nieuws/item/{id}', [NewsController::class, 'viewNewsItem'])->name('news.item');

Route::get('/nieuws/{id}', [NewsController::class, 'viewNewsItem'])->name('news.item');
Route::get('/nieuws/', [NewsController::class, 'viewNewsPage'])->name('news.list');

Route::get('/agenda/public/maand', [AgendaController::class, 'agendaMonthPublic'])->name('agenda.public.month');
Route::get('/agenda/public/overzicht', [AgendaController::class, 'agendaSchedulePublic'])->name('agenda.public.schedule');
Route::get('/agenda/public/activiteit/{id}', [AgendaController::class, 'agendaActivityPublic'])->name('agenda.public.activity');

Route::get('/contact', [NonLoggedInController::class, 'contact'])->name('contact');
Route::post('/contact', [NonLoggedInController::class, 'contactSubmit'])->name('contact.submit');

Route::post('/agenda/public/activiteit/{id}', [NonLoggedInController::class, 'handleActivityForm'])->name('agenda.activity.submit');

Route::get('/agenda/feed/{token}.ics', [AgendaController::class, 'exportFeed'])->name('agenda.feed');


//Admin
Route::middleware(['checkRole:Administratie'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'admin'])->name('admin');


    Route::get('/dashboard/nieuws', [AdminController::class, 'news'])->name('admin.news');
    Route::get('/dashboard/nieuws/details/{id}', [AdminController::class, 'newsDetails'])->name('admin.news.details');

    Route::get('/dashboard/nieuws/bewerk/{id}', [AdminController::class, 'newsEdit'])->name('admin.news.edit');
    Route::post('/dashboard/nieuws/bewerk/{id}', [AdminController::class, 'newsEditSave'])->name('admin.news.edit.save');

    Route::get('/dashboard/nieuws/publiceer/{id}', [AdminController::class, 'newsPublish'])->name('admin.news.publish');
    Route::get('/dashboard/nieuws/verwijder/{id}', [AdminController::class, 'newsDelete'])->name('admin.news.delete');

    Route::get('/dashboard/nieuws/nieuw-nieuwtje', [AdminController::class, 'newNews'])->name('admin.news.new');
    Route::post('/dashboard/nieuws/nieuw-nieuwtje', [AdminController::class, 'newsCreate'])->name('admin.news.new.create');

    // Account management
    Route::get('/dashboard/account-beheer', [AdminController::class, 'accountManagement'])->name('admin.account-management');

    Route::post('/dashboard/account-beheer/export', [AdminController::class, 'exportData'])->name('admin.account-management.export');

    Route::get('/dashboard/account-beheer/details/{id}', [AdminController::class, 'accountDetails'])->name('admin.account-management.details');

    Route::get('/dashboard/account-beheer/bewerk/{id}', [AdminController::class, 'editAccount'])->name('admin.account-management.edit');
    Route::post('/dashboard/account-beheer/bewerk/{id}', [AdminController::class, 'storeAccount'])->name('admin.account-management.store');

    Route::get('/dashboard/account-beheer/wachtwoord/{id}', [AdminController::class, 'editAccountPassword'])->name('admin.account-management.password');
    Route::post('/dashboard/account-beheer/wachtwoord/{id}', [AdminController::class, 'editAccountPasswordStore'])->name('admin.account-management.password.store');

    Route::get('/dashboard/account-beheer/verwijder/{id}', [AdminController::class, 'deleteAccount'])->name('admin.account-management.delete');


    // Create account
    Route::get('/dashboard/maak-account', [AdminController::class, 'createAccount'])->name('admin.create-account');
    Route::post('/dashboard/maak-account', [AdminController::class, 'createAccountStore'])->name('admin.create-account-store');


    // Role management
    Route::get('/dashboard/rol-beheer', [AdminController::class, 'roleManagement'])->name('admin.role-management');

    Route::get('/dashboard/rol-beheer/bewerk/{id}', [AdminController::class, 'editRole'])->name('admin.role-management.edit');
    Route::post('/dashboard/rol-beheer/bewerk/{id}', [AdminController::class, 'storeRole'])->name('admin.role-management.store');

    Route::get('/dashboard/rol-beheer/verwijder/{id}', [AdminController::class, 'deleteRole'])->name('admin.role-management.delete');

    Route::get('/dashboard/rol-beheer/nieuw', [AdminController::class, 'createRole'])->name('admin.role-management.create');
    Route::post('/dashboard/rol-beheer/nieuw', [AdminController::class, 'createRoleStore'])->name('admin.role-management.create.store');

    Route::get('/dashboard/logs', [AdminController::class, 'logs'])->name('admin.logs');


    Route::get('/dashboard/contact', [AdminController::class, 'contact'])->name('admin.contact');
    Route::get('/dashboard/contact/details/{id}', [AdminController::class, 'contactDetails'])->name('admin.contact.details');
    Route::get('/dashboard/contact/verwijder/{id}', [AdminController::class, 'contactDelete'])->name('admin.contact.delete');
    Route::get('/dashboard/contact/afgehandeld/{id}', [AdminController::class, 'contactSeen'])->name('admin.contact.seen');

});


//Agenda
//uitgelicht + connectie met accomodaties

//Agenda
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/agenda/maand', [AgendaController::class, 'agendaMonth'])->name('agenda.month');
    Route::get('/dashboard/agenda/overzicht', [AgendaController::class, 'agendaSchedule'])->name('agenda.schedule');

    Route::get('/dashboard/agenda/activiteit/{id}', [AgendaController::class, 'agendaActivity'])->name('agenda.activity');

    Route::get('/dashboard/agenda/activiteit/aanwezig/{id}/{user}', [AgendaController::class, 'agendaPresent'])->name('agenda.activity.present');
    Route::get('/dashboard/agenda/activiteit/niet-aanwezig/{id}/{user}', [AgendaController::class, 'agendaAbsent'])->name('agenda.activity.absent');

    Route::post('/dashboard/agenda/token', [AgendaController::class, 'generateToken']);

});

Route::middleware(['checkRole:Administratie'])->group(function () {
    Route::get('/dashboard/agenda/nieuw', [AgendaController::class, 'createAgenda'])->name('agenda.new');
    Route::post('/dashboard/agenda/nieuw', [AgendaController::class, 'createAgendaSave'])->name('agenda.new.create');

    Route::get('/dashboard/agenda/bewerken/{id}', [AgendaController::class, 'editActivity'])->name('agenda.edit.activity');
    Route::post('/dashboard/agenda/bewerken/{id}', [AgendaController::class, 'editActivitySave'])->name('agenda.edit.activity.save');

    Route::get('/dashboard/agenda/delete/{id}', [AgendaController::class, 'deleteActivity'])->name('agenda.delete');


    Route::get('/dashboard/agenda/aanwezigheid/{id}', [AgendaController::class, 'agendaPresenceActivity'])->name('agenda.presence.activity');
    Route::post('/dashboard/agenda/aanwezigheid/export', [AgendaController::class, 'exportPresenceData'])->name('agenda.presence.export');

    Route::get('/dashboard/agenda/inschrijvingen/{id}', [AgendaController::class, 'agendaSubmissionsActivity'])->name('agenda.submissions.activity');

});

Route::post('/upload-image', [ForumController::class, 'uploadImage'])->name('forum.image');
Route::post('/upload-pdf', [ForumController::class, 'uploadPdf'])->name('forum.pdf');
