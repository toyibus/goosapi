<?php 

/** 
 * Get header Authorization
 * */
function getAuthorizationHeader()
{
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

/**
* get access token from header
* */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function env($key, $default = "")
{
    return findWithKey(".env", $key, $default);
}

function findWithKey($file, $key, $default = "")
{
    $contents = file_get_contents($file);
    //$contents = explode(PHP_EOL, trim($contents));
    $contents = preg_split('/\r\n|\r|\n/', $contents);
    //dd($contents);
    foreach ($contents as $c)
    {
        if (!$c) continue;
        $line = explode('=', $c, 2);
   
        if ($line[0]==$key) return $line[1];
    }
    return $default;
}
