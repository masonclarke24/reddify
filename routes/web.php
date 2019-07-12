<?php

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

Route::get('/', function () {
    return view('app');
});

Route::post('/createVideo','PostController@createVideo');

Route::post('voices', 'VoiceController@index');

Route::post('/voiceSample', 'VoiceController@show');

Route::get('/download/{fileName}/{extention}', function ($filename, $extention){
    $path = getcwd() . '\video';

    //check if the file exists on the expected path
    if(file_exists($path ."\\" . $filename .'.'.$extention)){
        header("Content-Disposition : attachment; filename=" .$filename .'.'.$extention);
        readfile($path ."\\" . $filename .'.'.$extention);
        exit;
    }
    else{
        echo 'Files does not exist on the given path';
    }
});

Route::get('progress', function(){return session('progress', 0);});
