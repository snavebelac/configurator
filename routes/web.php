<?php

use App\Http\Middleware\RequireActiveUser;
use App\Livewire\Admin\Clients\ClientList;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Features\FeaturesList;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\Proposals\Preview;
use App\Livewire\Admin\Proposals\ProposalCreate;
use App\Livewire\Admin\Users\UserList;
use App\Livewire\ForgottenPassword;
use App\Livewire\Login;
use App\Livewire\PasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'users_count' => User::count()
    ]);
})->name('home');

Route::middleware(['throttle:authentication'])->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgotten-password', ForgottenPassword::class)->name('password.request');
    Route::get('/password-reset/{token}', PasswordReset::class)->name('password.reset');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('home');
})->name('logout');

// Admin area
Route::middleware(['auth', RequireActiveUser::class])->prefix('dashboard')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/profile', Profile::class)->name('dashboard.profile');
    Route::get('/users', UserList::class)->name('dashboard.users');
    Route::get('/features', FeaturesList::class)->name('dashboard.features');
    Route::get('/proposal/create', ProposalCreate::class)->name('dashboard.proposal.create');
    Route::get('/proposal/preview/{proposal:uuid}', Preview::class)->name('dashboard.proposal.preview');
    Route::get('/clients', ClientList::class)->name('dashboard.clients');
});

// View Proposals
