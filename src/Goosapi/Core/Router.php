<?php 
namespace Goosapi\Core;

use Goosapi\Core\Interfaces\IRouter;
use Goosapi\goosapi;
use Goosapi\Utils\ArrayUtils;
use Goosapi\Utils\StringUtils;

class Router implements IRouter
{
    private $_api;
    public function __construct(goosapi $api = null)
    {
        $this->_api = $api;
    }

    /* Implement function from IRouter */
    // Http Restful Type
    public function get($path, $obj)
    {
        if ($_SERVER['REQUEST_METHOD'] != "GET") return;

        $this->_route($path, $obj);
    }

    public function post($path, $obj)
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") return;

        $this->_route($path, $obj);
    }

    public function delete($path, $obj)
    {
        if ($_SERVER['REQUEST_METHOD'] != "DELETE") return;

        $this->_route($path, $obj);
    }

    public function patch($path, $obj)
    {
        if ($_SERVER['REQUEST_METHOD'] != "PATCH") return;

        $this->_route($path, $obj);
    }

    public function put($path, $obj)
    {
        if ($_SERVER['REQUEST_METHOD'] != "PUT") return;

        $this->_route($path, $obj);
    }

    public function option($path, $obj)
    {
        if ($_SERVER['REQUEST_METHOD'] != "OPTION") return;

        $this->_route($path, $obj);
    }

    // Router Functions
    public function group($path, $function)
    {
        $path = ltrim($path, '/');
        $query =  ltrim($_SERVER['REDIRECT_QUERY_STRING'], "args=");

        if (!StringUtils::startWith($query, $path)) return;
        $_SERVER['REDIRECT_QUERY_STRING'] = str_replace($path."/", "", $_SERVER['REDIRECT_QUERY_STRING']);
        
        $function($this->_api);
    }

    public function route($path, $obj, $routing)
    {
        if ($path=="/") $path="";
        // d("------------------------------------------------");
        // d($path);
        // d($obj);
        // d($routing);

        $tmp_path = $path;
        
        foreach ($routing as $key => $value)
        {
            if (empty($key)) continue ;
        
            $path = $tmp_path;
            $methods = explode("|", $value);


            // d($path);
            // d($methods);
            // dd(preg_replace("/\//", "$path/", "$key", 1));

            // dd(preg_match("/^\//", $key));
        
            // dd(preg_replace("/[\/]./", "$path/", "$key", 1));
            // dd(StringUtils::replaceFirst("/", "$key/", $path));
            
            // d("Key : $key");
            // d("Path : $path");

            if ($key[strlen($key) - 1] == "/")
            {
                $key = $key.explode('/', $key)[0];
            }
            
      
            // d("Key : $key");
            // d("Path : $path");

            // if ($key[0] != "/" && $key[strlen($key) - 1] != "/")
            //     $path = StringUtils::replaceFirst("/", "$key/", $path);
            // else
            if (count(explode("/", $key)) == 1)
                $path = StringUtils::replaceFirst("/", "$key/", $path);
            else
                $path = StringUtils::replaceFirst("/", "$path/", $key);
  
            // dd($path);
            // dd($obj);

            foreach ($methods as $method)
            {
                switch ($method)
                {
                    case "GET":
                        $this->get($path, $obj);
                    break;
                    case "POST":
                        $this->post($path, $obj);
                    break;
                    case "PUT":
                        $this->put($path, $obj);
                    break;
                    case "PATCH":
                        $this->patch($path, $obj);
                    break;
                    case "DELETE":
                        $this->delete($path, $obj);
                    break;
                    case "OPTION":
                        $this->option($path, $obj);
                    break;
                }
            }
        }
    }


    private function _route($path, $obj)
    {
        // d("---------------");
        // d("Path : $path");
        // d("obj : ", $obj);

        if (gettype($obj) == "string")
        {
            if (!class_exists($obj)) return;
            $class = new $obj();
            $obj = $class;
            // d($class);
        }
       
       
        $query  = str_replace("args=", "", $_SERVER['REDIRECT_QUERY_STRING']);
        if (strpos($query, "&"))
            $query = substr($query, 0, strpos($query, "&"));
       
        // d($_SERVER['REDIRECT_QUERY_STRING']);
        // d("Path -> " . $path);
        // d("Query -> " . $query);

        $paths      = explode('/', $path);
        $query      = explode('/', $query);
       

        // d("Paths -> ", $paths);
        // d("Query -> ", $query);

        // $func_name = array_shift($paths);
        // if (!empty($func_name)) array_shift($query);

        // $func_name = array_shift($paths);
        // if (!empty($paths[0])) $func_name = array_shift($paths);

        $func_name = empty($paths[0]) ? "" : array_shift($paths);

        // d("Paths -> ", $paths);
        // d("Query -> ", $query);

        if (count($paths) != count($query)) return;

        $urls = array_combine($paths, $query);

        // d($urls);
        if (!ArrayUtils::isMatchAssoc($urls, "/^:/")) return;

        // d($urls);
        // d("----  Matched (query, path)  ----");
        $payload = ArrayUtils::findByKey($urls, "/^:/");
        // d($payload);
        
        array_unshift($payload, $this->_api);
        if ($func_name)
            call_user_func_array(array($obj, $func_name), $payload); 
        else
            call_user_func_array($obj, $payload);
 
        
    }


}
?>