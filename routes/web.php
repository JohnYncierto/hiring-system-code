<?php

use App\Http\Controllers\Admin\PipelineTemplateController;
use App\Http\Controllers\Admin\ScorecardTemplateController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');

        Route::get('scorecard-templates', [ScorecardTemplateController::class, 'index'])->name('scorecard-templates.index');
        Route::get('scorecard-templates/create', [ScorecardTemplateController::class, 'create'])->name('scorecard-templates.create');
        Route::post('scorecard-templates', [ScorecardTemplateController::class, 'store'])->name('scorecard-templates.store');
        Route::get('scorecard-templates/{scorecardTemplate}/edit', [ScorecardTemplateController::class, 'edit'])->name('scorecard-templates.edit');
        Route::put('scorecard-templates/{scorecardTemplate}', [ScorecardTemplateController::class, 'update'])->name('scorecard-templates.update');
        Route::delete('scorecard-templates/{scorecardTemplate}', [ScorecardTemplateController::class, 'destroy'])->name('scorecard-templates.destroy');

        Route::get('pipeline-templates', [PipelineTemplateController::class, 'index'])->name('pipeline-templates.index');
        Route::get('pipeline-templates/create', [PipelineTemplateController::class, 'create'])->name('pipeline-templates.create');
        Route::post('pipeline-templates', [PipelineTemplateController::class, 'store'])->name('pipeline-templates.store');
        Route::get('pipeline-templates/{pipelineTemplate}/edit', [PipelineTemplateController::class, 'edit'])->name('pipeline-templates.edit');
        Route::put('pipeline-templates/{pipelineTemplate}', [PipelineTemplateController::class, 'update'])->name('pipeline-templates.update');
        Route::delete('pipeline-templates/{pipelineTemplate}', [PipelineTemplateController::class, 'destroy'])->name('pipeline-templates.destroy');
    });

require __DIR__.'/settings.php';
