<?php

use Illuminate\Support\Facades\Route;
use Treii28\Grampsdb\Http\Controllers\GrampersController;

Route::middleware('web')
    //->namespace($this->namespace)
    ->prefix('gramps')
    ->group(
        function () {
            Route::resource('grampers', GrampersController::class);
        }
    );
