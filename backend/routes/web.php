<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\WorkoutController;
$router->options('{any:.*}', function () {
    return response('', 200);
});
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/workouts', 'WorkoutController@workouts');
$router->get('/workouts/stats', 'WorkoutController@stats');
$router->get('/workouts/{id}', 'WorkoutController@show');

$router->post('/workouts', 'WorkoutController@store');
$router->patch('/workouts/{id}', 'WorkoutController@update');
$router->delete('/workouts/{id}', 'WorkoutController@delete');

$router->post('/upload_image', function () use ($router) {
    $request = $router->app->make('request');

    if (!$request->hasFile('image')) {
        return response()->json(['success' => false, 'message' => 'No file']);
    }

    $file = $request->file('image');
    $filename = uniqid() . '.' . $file->getClientOriginalExtension();

    $uploadDir = base_path('public/storage/uploads');
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $file->move($uploadDir, $filename);

    return response()->json(['success' => true, 'path' => 'storage/uploads/' . $filename]);
});
