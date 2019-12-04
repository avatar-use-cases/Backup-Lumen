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

    #RG 2019-12-04 Search locations for matching classes
    $search_folder_cco = "avatar_cco_files/CommonCoreOntologies";
    $search_folder_imports = "$search_folder_cco/imports";

    if($DEBUG !== false) {
        echo "KH DEBUG: Route is ---------------> " . $any . "</br>";
    }

    if ($any === 'Visual') {
        if ($DEBUG !== false) {
            echo "KH: GOTO VISUAL</br>";
        }
        return $router->app->visual_map();
    }

    // break anything following the home URL into substrings to be parsed
    // The "explode()" feature of PHP is great for splitting on all '/'
    // to get our substrings IE http://www.ontologyrepository.com would not
    // trigger but http://www.ontologyrepository.com/CommonCoreOntologies/Mid/AgentOntology
    // will give us the substrings:
    // 1) CommonCoreOntologies
    // 2) Mid
    // 3) AgentOntology
    // Now since we wish to limit the terms to only two we use the last parameter
    // to only have final array containing two elements, the first substring and
    // the remainder IE explode('char', 'string', 'limit');
    // so now we will get:
    // 1) CommonCoreOntologies
    // 2) Mid/AgentOntology
    list($abbreviation, $search_term) = explode('/', $any, 2);
#    $search_term = $any;

    if ($DEBUG !== false) {
            echo "KH DEBUG: abbreviation -----------> $abbreviation</br>";
            echo "KH DEBUG: search_term ------------> $search_term</br>";
    }

    // Case where extension is there IE
    // www.ontologyrepository.com/CommonCoreOntologies/Mid/AgentOntology.ttl
    if(strpos($search_term, '.') !== false) {
        // Similar to the above explode on the address we will now use explode
        // to check for any .<ext> at the end of the url (IE .ttl)
        list($search_term_final, $file_extension) = explode('.', $search_term);

        if ($DEBUG !== false) {
            echo "KH DEBUG: search_term_final ------> $search_term_final</br>";
            echo "KH DEBUG: file_extension ---------> $file_extension</br>";
            echo "KH DEBUG: Triggered file extension search</br>";
        }

        // Do not honor non-ttl extension searches
        if ($file_extension === 'ttl') {
            #RG 2019-12-04 Search imports first since it will be more unique
            // Check first line in the file
            $ret = $router->app->search_firstline_extension(
                                                         $search_term_final,
                                                         $search_folder_imports,
                                                         $output);
            if ($ret != 1) {
               $ret = $router->app->search_firstline_extension(
                                                            $search_term_final,
                                                            $search_folder_cco,
                                                            $output);
            }
            if ($ret !== 1) {
                // Check all files/all contents if not found in any first-line
                // NOTE: Talk to Bob about doing full file search...?
                $ret = $router->app->search_whole_file_extension(
                                                         $search_term_final,
                                                         $search_folder_imports,
                                                         $output);
            }
            if ($ret !== 1) {
                $ret = $router->app->search_whole_file_extension(
                                                             $search_term_final,
                                                             $search_folder_cco,
                                                             $output);
            }
        } else {
            $output = "<html><HTTP/1.0 404 Not Found>ERROR: CCO ";
            $output .= "\".$file_extension\" is not a valid file extension";
            $output .= ", try .ttl</br></html>";
        }
    } else { // No file extension case
        if ($DEBUG !== false) {
            echo "KH DEBUG: Triggered normal search</br>";
        }
        #RG 2019-12-04 Search imports first since it will be more unique
        // Check first line in the file
        $ret = $router->app->search_firstline($search_term,
                                              $search_folder_imports,
                                              $output);

        if ($ret !== 1) {
            $ret = $router->app->search_firstline($search_term,
                                                  $search_folder_cco,
                                                  $output);
        }
        if ($ret !== 1) {
            // Check all files/all contents if not found in any first-line
            // NOTE: Talk to Bob about doing full file search...?
            $ret = $router->app->search_whole_file($search_term,
                                                   $search_folder_imports,
                                                   $output);
        }
        if ($ret !== 1) {
            $ret = $router->app->search_whole_file($search_term,
                                                   $search_folder_cco,
                                                   $output);
        }
    }

#    print_r("KH: CHECKING HEADERS!</br>");
#    print_r( apache_request_headers() );
#    print_r("</br>KH: CHECKING HEADERS DONE!</br>");

# RG 2019-12-03 - fixed bug: if only one member in accept header, throws error

    $accept_header = $_SERVER['HTTP_ACCEPT'];
    #list($preferred_type, $other_types) = explode(',', $accept_header, 2);
    $preferred_type = "application/x-turtle";
    $other_types = "";
    $accept_types = explode(',', $accept_header, 2);
    if (count($accept_types) > 0) {
        $preferred_type = $accept_types[0];
        if (count($accept_types) > 1) {
            $other_types = $accept_types[1];
        }
    }

    if ($DEBUG !== false) {
            echo "KH DEBUG: Accept Header = " . $accept_header . "</br>";
            echo "KH DEBUG: preferred_type -----------> $preferred_type</br>";
            echo "KH DEBUG: other_types ------------> $other_types</br>";
    }

    if (strpos("text/html", $preferred_type) !== false ||
        strpos("application/xml", $preferred_type) !== false) {
        /* Assume this is a browser and return html format */
        return response($output)
                    ->withHeaders([
                    'Content-Type' => "text/html; charset=UTF-8"
                ]);
    } elseif (strpos("application/x-turtle", $preferred_type) !== false ||
              strpos("text/turtle", $preferred_type) !== false) {
        /* return turtle */
        return response($output)
                    ->withHeaders([
                    'Content-Type' => "application/x-turtle; charset=UTF-8"
                ]);
    } else {
        /* If the Accept header starts with application/rdf+xml, or if there is
         * no Accept header, return the rdf/xml version of the file with
         * appropriate escapes.  */
        $output = str_replace("<", "&lt", $output, $i);
        $output = str_replace(">", "&gt", $output, $i);
        return response($output)
                    ->withHeaders([
                    'Content-Type' => "rdf/xml; charset=UTF-8"
                ]);
    }
});
