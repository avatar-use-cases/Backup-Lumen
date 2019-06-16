<?php

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

#$router->get('/test', function () use ($router) {
#    return 'test';
#}

$router->get('/{any:.*}', function ($any) use ($router) {

    $DEBUG  = false; # Set to true to enable debugging messages
    $output = "NOTHING";
    $ret    = 0;

    if($DEBUG !== false) {
        echo "KH DEBUG: Route is ---------------> " . $any . "</br>";
    }

    list($abbreviation, $search_term) = explode('/', $any, 2);

    if ($DEBUG !== false) {
        echo "KH DEBUG: abbreviation -----------> $abbreviation</br>";
        echo "KH DEBUG: search_term ------------> $search_term</br>";
    }

    if(strpos($search_term, '.') !== false) {
        // explodable
        list($search_term_final, $file_extension) = explode('.', $search_term);

        if ($DEBUG !== false) {
            echo "KH DEBUG: search_term_final ------> $search_term_final</br>";
            echo "KH DEBUG: file_extension ---------> $file_extension</br>";
        echo "KH DEBUG: Triggered file extension search</br>";
        }

        $ret = $router->app->search_firstline_extension($search_term_final, $output);
        if ($ret !== 1) {
            $ret = $router->app->search_whole_file_extension($search_term_final, $output);
        }
    } else {
        // not explodable
        if ($DEBUG !== false) {
            echo "KH DEBUG: Triggered normal search</br>";
        }
        $ret = $router->app->search_firstline($search_term, $output);
        if ($ret !== 1) {
            $ret = $router->app->search_whole_file($search_term, $output);
        }
    }

    return "$output";

});
