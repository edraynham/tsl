<?php

use App\Http\Controllers\Account\InstructorProfileController;
use App\Http\Controllers\Auth\MagicLoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\GroundCompetitionController;
use App\Http\Controllers\Owner\GroundOpeningHoursController;
use App\Http\Controllers\Owner\GroundProfileController;
use App\Http\Controllers\ShootingGroundController;
use App\Models\Competition;
use App\Models\ShootingGround;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $premierGrounds = ShootingGround::query()
        ->with(['disciplines', 'facilities'])
        ->orderBy('name')
        ->limit(3)
        ->get();

    $upcomingCompetitions = Competition::query()
        ->with(['shootingGround', 'canonicalDiscipline'])
        ->where('starts_at', '>=', now()->startOfDay())
        ->orderBy('starts_at')
        ->limit(6)
        ->get();

    return view('home', compact('premierGrounds', 'upcomingCompetitions'));
})->name('home');

Route::get('/grounds/suggest', [ShootingGroundController::class, 'suggest'])
    ->middleware('throttle:60,1')
    ->name('grounds.suggest');
Route::get('/grounds', [ShootingGroundController::class, 'index'])->name('grounds.index');
Route::get('/grounds/{shooting_ground:slug}', [ShootingGroundController::class, 'show'])->name('grounds.show');

Route::get('/competitions', [CompetitionController::class, 'index'])->name('competitions.index');
Route::get('/competitions/{competition:slug}', [CompetitionController::class, 'show'])->name('competitions.show');

Route::get('/instructors', [InstructorController::class, 'index'])->name('instructors.index');
Route::get('/instructors/{instructor:slug}', [InstructorController::class, 'show'])->name('instructors.show');

Route::view('/about', 'about')->name('about');
Route::view('/privacy', 'privacy')->name('privacy');

Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/login', [MagicLoginController::class, 'create'])->name('login');
Route::post('/login', [MagicLoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::get('/login/verify/{token}', [MagicLoginController::class, 'verify'])->name('login.verify')->where('token', '[A-Za-z0-9]+');
Route::post('/logout', [MagicLoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/account', function () {
    return auth()->check()
        ? view('account.index')
        : view('auth.account');
})->name('account');

Route::middleware('auth')->group(function (): void {
    Route::get('/account/instructor', InstructorProfileController::class)->name('account.instructor');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/register/roles', [RegisterController::class, 'editRoles'])->name('register.roles');
    Route::post('/register/roles', [RegisterController::class, 'updateRoles'])->middleware('throttle:10,1')->name('register.roles.store');
});

Route::get('/email/verify', function () {
    if (request()->user()?->hasVerifiedEmail()) {
        return redirect()->route('home');
    }

    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    $user = $request->user()->fresh();

    if ($user->registration_roles_completed_at === null) {
        return redirect()
            ->route('register.roles')
            ->with('status', __('Thanks — your email is verified. Tell us how you’ll use the site.'));
    }

    return redirect()->route('home')->with('status', __('Thanks — your email is verified.'));
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', __('A fresh verification link has been sent to your email address.'));
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware(['auth', 'verified'])->prefix('owner')->name('owner.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/grounds/{shooting_ground:slug}/edit', [GroundProfileController::class, 'edit'])->name('grounds.edit');
    Route::put('/grounds/{shooting_ground:slug}', [GroundProfileController::class, 'update'])->name('grounds.update');
    Route::get('/grounds/{shooting_ground:slug}/opening-hours', [GroundOpeningHoursController::class, 'edit'])->name('grounds.opening-hours.edit');
    Route::put('/grounds/{shooting_ground:slug}/opening-hours', [GroundOpeningHoursController::class, 'update'])->name('grounds.opening-hours.update');
    Route::get('/grounds/{shooting_ground:slug}/competitions', [GroundCompetitionController::class, 'index'])->name('grounds.competitions.index');
    Route::get('/grounds/{shooting_ground:slug}/competitions/create', [GroundCompetitionController::class, 'create'])->name('grounds.competitions.create');
    Route::post('/grounds/{shooting_ground:slug}/competitions', [GroundCompetitionController::class, 'store'])->name('grounds.competitions.store');
    Route::get('/grounds/{shooting_ground:slug}/competitions/{competition}/edit', [GroundCompetitionController::class, 'edit'])->name('grounds.competitions.edit');
    Route::put('/grounds/{shooting_ground:slug}/competitions/{competition}', [GroundCompetitionController::class, 'update'])->name('grounds.competitions.update');
    Route::delete('/grounds/{shooting_ground:slug}/competitions/{competition}', [GroundCompetitionController::class, 'destroy'])->name('grounds.competitions.destroy');
});
