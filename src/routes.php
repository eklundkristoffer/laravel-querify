<?php

use Illuminate\Http\Request;

Route::get('/querify', function (Request $request) {
    $class = $request->get('query');

    return (new $class)->get();
});
