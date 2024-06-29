<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\admin\DasboardController;
use App\Http\Controllers\admin\JobApplicationController;
use App\Http\Controllers\admin\JobController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobsController;
use App\Models\JobApplication;
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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index'])->name('front.home');
Route::get('/jobs', [JobsController::class, 'index'])->name('front.index');
Route::get('/jobs/detail/{id}', [JobsController::class, 'detail'])->name('front.detail');
Route::post('/applyJob', [JobsController::class, 'applyJob'])->name('front.applyJob');
Route::post('/saveJobs', [JobsController::class, 'saveJobs'])->name('front.saveJobs');

Route::get('/forgot-password', [AccountController::class, 'forgotPassword'])->name('account.forgotPassword');
Route::post('/process-forgot-password', [AccountController::class, 'processForgetPassword'])->name('account.processForgetPassword');
Route::get('/reset-password/{token}', [AccountController::class, 'resetPassword'])->name('account.resetPassword');
Route::post('/process-reset-password', [AccountController::class, 'processResetPassword'])->name('account.processResetPassword');






Route::group(['prefix' => 'admin', 'middleware' => 'checkRole'], function () {

    Route::get('/dashboard', [DasboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('/users/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/update-users/{id}', [UserController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/delete-user', [UserController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/jobs', [JobController::class, 'index'])->name('admin.jobs');
    Route::get('/jobs/edit/{id}', [JobController::class, 'edit'])->name('admin.jobs.edit');
    Route::put('/jobs/{id}', [JobController::class, 'update'])->name('admin.jobs.update');
    Route::delete('/delete-job', [JobController::class, 'destroy'])->name('admin.jobs.delete');
    Route::get('/job-applications', [JobApplicationController::class, 'index'])->name('admin.job_application');
    Route::delete('/delete-job-application', [JobApplicationController::class, 'destroy'])->name('admin.job_application.delete');
});

Route::group(['acount'], function () {

    //Guest Route
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/account/register', [AccountController::class, 'registration'])->name('account.registration');
        Route::post('/account/process-register', [AccountController::class, 'processRegistration'])->name('account.processRegistration');
        Route::get('/account/login', [AccountController::class, 'login'])->name('account.login');
        Route::post('/account/authenticate', [AccountController::class, 'authenticate'])->name('account.authenticate');
    });

    //Authenticate Routes
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
        Route::put('/update-profile', [AccountController::class, 'updateProfile'])->name('account.updateProfile');
        Route::get('/account/logout', [AccountController::class, 'logout'])->name('account.logout');
        Route::post('/update-profile-pic', [AccountController::class, 'updateProfilePic'])->name('account.updateProfilePic');
        Route::get('/account/create-job', [AccountController::class, 'createJob'])->name('account.createJob');
        Route::post('/saveJob', [AccountController::class, 'saveJob'])->name('account.saveJob');
        Route::get('/myJob', [AccountController::class, 'myJobs'])->name('account.myJobs');
        Route::get('/my-jobs/edit/{jobId}', [AccountController::class, 'editJob'])->name('account.editJob');
        Route::post('/update-job/{jobId}', [AccountController::class, 'updateJob'])->name('account.updateJob');
        Route::post('/delete-job', [AccountController::class, 'deleteJob'])->name('account.deleteJob');
        Route::get('/my-job-application', [AccountController::class, 'myJobApplications'])->name('account.myJobApplications');

        Route::post('/remove-job-applications', [AccountController::class, 'removeJobs'])->name('account.removeJobs');
        Route::get('/saved-jobs', [AccountController::class, 'savedJobs'])->name('account.savedJobs');
        Route::post('/remove-saved-jobs', [AccountController::class, 'removeSavedJobs'])->name('account.removeSavedJobs');
        Route::post('/update-password', [AccountController::class, 'updatePasword'])->name('account.updatePassword');
    });
});
