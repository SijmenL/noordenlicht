<?php

use App\Http\Controllers\AccommodatieController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NonLoggedInController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TicketController;
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

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/nieuws/item/{id}', [NewsController::class, 'viewNewsItem'])->name('news.item');

Route::get('/nieuws/{id}', [NewsController::class, 'viewNewsItem'])->name('news.item');
Route::get('/nieuws/', [NewsController::class, 'viewNewsPage'])->name('news.list');

Route::get('/agenda/maand', [AgendaController::class, 'agendaMonthPublic'])->name('agenda.public.month');
Route::get('/agenda/overzicht', [AgendaController::class, 'agendaSchedulePublic'])->name('agenda.public.schedule');
Route::get('/agenda/activiteit/{id}', [AgendaController::class, 'agendaActivityPublic'])->name('agenda.public.activity');

Route::get('/contact', [NonLoggedInController::class, 'contact'])->name('contact');
Route::post('/contact', [NonLoggedInController::class, 'contactSubmit'])->name('contact.submit');

Route::post('/agenda/public/activiteit/{id}', [NonLoggedInController::class, 'handleActivityForm'])->name('agenda.activity.submit');

Route::get('/agenda/feed/{token}.ics', [AgendaController::class, 'exportFeed'])->name('agenda.feed');

Route::get('/contact', [NonLoggedInController::class, 'contact'])->name('contact');

Route::get('/accommodaties', [AccommodatieController::class, 'home'])->name('accommodaties');
Route::get('/accommodaties/aanvraagformulier', [AccommodatieController::class, 'form'])->name('accommodaties.form');
Route::get('/accommodaties/aanvraagformulier/success', [AccommodatieController::class, 'formSuccess'])->name('accommodaties.form.success');
Route::get('/accommodaties/{id}', [AccommodatieController::class, 'details'])->name('accommodaties.details');

Route::get('/accommodatie/{id}/beschikbaarheid', [App\Http\Controllers\AccommodatieController::class, 'getMonthlyAvailability'])->name('accommodatie.availability');
Route::post('/accommodatie/check-beschikbaarheid', [App\Http\Controllers\AccommodatieController::class, 'checkAvailability'])->name('accommodatie.check_availability');

Route::get('/winkel', [ProductController::class, 'shop'])->name('shop');
Route::get('/winkel/product/{id}', [ProductController::class, 'details'])->name('shop.details');

// Shopping Cart
Route::get('/winkelmandje', [CartController::class, 'index'])->name('cart.index');
Route::post('/winkelmandje/toevoegen/{id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/winkelmandje/bewerken/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/winkelmandje/verwijderen/{id}', [CartController::class, 'remove'])->name('cart.remove');

Route::post('/winkelmandje/bulk-toevoegen', [OrderController::class, 'bulkAdd'])->name('cart.bulk_add');

Route::get('/afrekenen', [OrderController::class, 'checkout'])->name('checkout');
Route::post('/afrekenen', [OrderController::class, 'store'])->name('checkout.store');
Route::get('/bestelling/success/{order_number}', [OrderController::class, 'success'])->name('order.success');
Route::get('/afrekenen/{id}/retry', [OrderController::class, 'retry'])->name('order.retry');

Route::get('/ticket/download/{ticket_uuid}', [TicketController::class, 'download'])->name('ticket.download');
Route::get('/tickets/stream/{ticket_uuid}/', [TicketController::class, 'streamPdf'])->name('admin.tickets.stream');

Route::post('/webhooks/mollie', [OrderController::class, 'handleWebhook'])->name('webhooks.mollie');

Route::post('/user-search', [ForumController::class, 'searchUser'])->name('search-user');

// Bookings

Route::middleware(['checkAccepted',])->group(function () {
    Route::post('/accommodatie/boeken/', [App\Http\Controllers\AccommodatieController::class, 'storeBooking'])->name('accommodatie.store_booking');
    Route::get('/accommodatie/boeken/{id}', [App\Http\Controllers\AccommodatieController::class, 'book'])->name('accommodatie.book');
});

Route::middleware(['auth',])->group(function () {
    Route::get('/instellingen', [SettingsController::class, 'account'])->name('user.settings');

    Route::get('/instellingen/account/bewerk', [SettingsController::class, 'editAccount'])->name('user.settings.account.edit');
    Route::post('/instellingen/account/bewerk', [SettingsController::class, 'editAccountSave'])->name('user.settings.account.store');

    Route::get('/instellingen/verander-wachtwoord', [SettingsController::class, 'changePassword'])->name('user.settings.change-password');
    Route::post('/instellingen/verander-wachtwoord', [SettingsController::class, 'updatePassword'])->name('user.settings.change-password.store');

    Route::get('/instellingen/notificaties', [SettingsController::class, 'notifications'])->name('user.settings.edit-notifications');
    Route::post('/instellingen/notificaties', [SettingsController::class, 'notificationsSave'])->name('user.settings.edit-notifications.store');


    Route::get('/instellingen/bestellingen', [SettingsController::class, 'showOrders'])->name('user.orders');
    Route::get('/instellingen/bestellingen/{id}', [SettingsController::class, 'orderDetails'])->name('user.orders.details');

    Route::get('/instellingen/boekingen', [AgendaController::class, 'showBookings'])->name('user.bookings');
    Route::get('/instellingen/boekingen/{id}', [SettingsController::class, 'bookingDetails'])->name('user.bookings.details');
});


//Admin
Route::middleware(['checkRole:Administratie'])->group(function () {
    Route::post('/prices/link', [PriceController::class, 'linkPrice'])->name('admin.prices.link');
    Route::delete('/prices/unlink/{priceLink}', [PriceController::class, 'unlinkPrice'])->name('admin.prices.unlink');

    Route::get('/dashboard', [AdminController::class, 'admin'])->name('admin');

    Route::get('/dashboard/debug/mail', [AdminController::class, 'debugMail'])->name('admin.debug.mail');
    Route::get('/dashboard/debug/mail/{id}', [AdminController::class, 'mail'])->name('admin.debug.mail.view');


    Route::get('/dashboard/nieuws', [AdminController::class, 'news'])->name('admin.news');
    Route::get('/dashboard/nieuws/details/{id}', [AdminController::class, 'newsDetails'])->name('admin.news.details');

    Route::get('/dashboard/nieuws/bewerk/{id}', [AdminController::class, 'newsEdit'])->name('admin.news.edit');
    Route::post('/dashboard/nieuws/bewerk/{id}', [AdminController::class, 'newsEditSave'])->name('admin.news.edit.save');

    Route::get('/dashboard/nieuws/publiceer/{id}', [AdminController::class, 'newsPublish'])->name('admin.news.publish');
    Route::get('/dashboard/nieuws/verwijder/{id}', [AdminController::class, 'newsDelete'])->name('admin.news.delete');

    Route::get('/dashboard/nieuws/nieuw-nieuwtje', [AdminController::class, 'newNews'])->name('admin.news.new');
    Route::post('/dashboard/nieuws/nieuw-nieuwtje', [AdminController::class, 'newsCreate'])->name('admin.news.new.create');

    Route::get('/dashboard/mail', [AdminController::class, 'notifications'])->name('admin.notifications');
    Route::post('/dashboard/mail', [AdminController::class, 'notificationsSend'])->name('admin.notifications.send');

    // products
    Route::get('/dashboard/producten', [ProductController::class, 'index'])->name('admin.products');

    Route::get('/dashboard/producten/new', [ProductController::class, 'create'])->name('admin.products.new');
    Route::post('/dashboard/producten/new', [ProductController::class, 'store'])->name('admin.products.new.save');

    Route::get('/dashboard/producten/{id}', [ProductController::class, 'productDetails'])->name('admin.products.details');

    Route::get('/dashboard/producten/{id}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::post('/dashboard/producten/{id}/edit', [ProductController::class, 'update'])->name('admin.products.edit.save');

    Route::get('/dashboard/producten/delete/{id}', [ProductController::class, 'destroy'])->name('admin.products.delete');

    Route::post('/dashboard/producten/temp/image', [ProductController::class, 'uploadTempImage']);

    Route::delete('/dashboard/producten/temp/image/{id}', [ProductController::class, 'deleteTempImage']);

    // Orders
    Route::get('/dashboard/bestellingen', [OrderController::class, 'list'])->name('admin.orders');
    Route::get('/dashboard/bestellingen/{id}', [OrderController::class, 'details'])->name('admin.orders.details');

    Route::post('/dashboard/bestellingen/{id}', [OrderController::class, 'updateStatus'])->name('admin.orders.details.update');

    // Bookings
    Route::get('/dashboard/boekingen', [BookingController::class, 'list'])->name('admin.bookings');
    Route::get('/dashboard/boekingen/{id}', [BookingController::class, 'details'])->name('admin.bookings.details');

    Route::post('/dashboard/boekingen/{id}', [BookingController::class, 'updateStatus'])->name('admin.bookings.details.update');

    Route::get('/dashboard/aanmeldingen', [AdminController::class, 'signup'])->name('admin.signup');
    Route::get('/dashboard/aanmeldingen/details/{id}', [AdminController::class, 'signupAccountDetails'])->name('admin.signup.details');

    Route::get('/dashboard/aanmeldingen/accepteer/{id}', [AdminController::class, 'signupAccept'])->name('admin.signup.accept');
    Route::get('/dashboard/aanmeldingen/verwijder/{id}', [AdminController::class, 'signupDelete'])->name('admin.signup.delete');


    // Tickets
    Route::get('/dashboard/tickets/scan', [TicketController::class, 'scanTickets'])->name('admin.tickets.scan');

    Route::post('/dashboard/tickets/scan', [TicketController::class, 'check'])->name('admin.tickets.check');

    Route::post('/dashboard/tickets/check/{uuid}/checkin/', [TicketController::class, 'checkIn'])->name('admin.tickets.checkin');
    Route::post('/dashboard/tickets/check/{uuid}/cancel/', [TicketController::class, 'cancel'])->name('admin.tickets.cancel');

    Route::get('/dashboard/tickets', [TicketController::class, 'list'])->name('admin.tickets.list');
    Route::get('/dashboard/tickets/{uuid}', [TicketController::class, 'details'])->name('admin.tickets.details');

    Route::post('/dashboard/tickets/{uuid}', [TicketController::class, 'updateStatus'])->name('admin.tickets.details.update');


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

Route::middleware(['checkRole:Administratie'])->group(function () {
    Route::get('/dashboard/accommodaties', [AccommodatieController::class, 'accommodaties'])->name('admin.accommodaties');

    Route::get('/dashboard/accommodaties/nieuw', [AccommodatieController::class, 'createAccommodatie'])->name('admin.accommodaties.new');
    Route::post('/dashboard/accommodaties/nieuw', [AccommodatieController::class, 'createAccommodatieSave'])->name('admin.accommodaties.new.save');

    Route::get('/dashboard/accommodaties/details/{id}', [AccommodatieController::class, 'accommodatieDetails'])->name('admin.accommodaties.details');
    Route::get('/dashboard/accommodaties/bewerk/{id}', [AccommodatieController::class, 'editAccommodatie'])->name('admin.accommodaties.edit');
    Route::post('/dashboard/accommodaties/bewerk/{id}', [AccommodatieController::class, 'editAccommodatieSave'])->name('admin.accommodaties.edit.save');

    Route::get('/dashboard/accommodaties/delete/{id}', [AccommodatieController::class, 'deleteAccommodatie'])->name('admin.accommodaties.delete');


    Route::post('/dashboard/accommodaties/temp/icon', [AccommodatieController::class, 'uploadTempIcon'])->name('admin.accommodaties.temp.icon');
    Route::post('/dashboard/accommodaties/temp/image', [AccommodatieController::class, 'uploadTempImage'])->name('admin.accommodaties.temp.image');
    Route::delete('/dashboard/accommodaties/temp/image/{image}', [AccommodatieController::class, 'deleteTempImage'])->name('admin.accommodaties.temp.image.delete');
    Route::delete('/dashboard/accommodaties/temp/icon/{icon}', [AccommodatieController::class, 'deleteTempIcon'])->name('admin.accommodaties.temp.icon.delete');
});

Route::post('/upload-image', [ForumController::class, 'uploadImage'])->name('forum.image');
Route::post('/upload-pdf', [ForumController::class, 'uploadPdf'])->name('forum.pdf');
