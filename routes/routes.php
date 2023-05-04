<?php

use Illuminate\Support\Facades\Route;
use Treii28\Grampsdb\GrampsdbHelper;
use Treii28\Grampsdb\Http\Controllers\SVGController;
use Treii28\Grampsdb\Http\Controllers\PersonController;
use Treii28\Grampsdb\Http\Controllers\GrampersController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('web')
    //->namespace($this->namespace)
    ->prefix('gramps')
    ->group(function () {
        Route::get('/', function () { return view('welcome'); });

        Route::get('/woodgen', function () { return view('pages.woodgen'); });
        Route::get('/woodang', function () { return view('pages.woodang'); });
        Route::resource('persons', PersonController::class);
        Route::resource('grampers', GrampersController::class);
    }
);

Route::prefix('grampsapi')
    ->middleware('api')
    //->namespace($this->namespace)
    ->group(function () {
        Route::get('/persons', function() {
            // If the Content-Type and Accept headers are set to 'application/json',
            // this will return a JSON structure. This will be cleaned up later.
            $gPersons = GrampsdbHelper::utf8ize(GrampsdbHelper::getPersons());
            $gpJson = json_encode($gPersons);
            if($gpJson == false) $gpJson = [ 'error' => json_last_error_msg() ];
            return $gpJson;
        });
        Route::get('/persons/{id}', function($id) {
            $person = GrampsdbHelper::utf8ize(GrampsdbHelper::getPersonById($id));
            $pJson = json_encode($person);
            if($pJson == false) $pJson = [ 'error' => json_last_error_msg() ];
            return $pJson;
        });
        Route::get('/persons/{id}/media', function($id) {
            $media = GrampsdbHelper::utf8ize(GrampsdbHelper::getMediaByPersonId($id));
            $pmJson = json_encode($media);
            if($pmJson == false) $pmJson = [ 'error' => json_last_error_msg() ];
            return $pmJson;
        });
    }
);
