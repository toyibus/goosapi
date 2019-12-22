<?php 
namespace Goosapi\Core;

use Closure;
use Goosapi\Core\Interfaces\IDumper;
use Goosapi\Core\Interfaces\IRouter;
use Goosapi\goosapi;
use Goosapi\Utils\ArrayUtils;
use Goosapi\Utils\StringUtils;

class Router implements IRouter, IDumper
{
    private $_api;
    private $_routing_dump; 
    private $group_path;

    public function __construct(goosapi $api = null)
    {
        $this->_api             = $api;
        $this->_routing_dump    = [];
        // dd( $_SERVER['REDIRECT_QUERY_STRING']);
        $_SERVER['REDIRECT_QUERY_STRING'] = str_replace("args=", "", $_SERVER['REDIRECT_QUERY_STRING']);
        // dd( $_SERVER['REDIRECT_QUERY_STRING']);
    }

    private function pushDumb($method, $path, $obj, CredentialProvider $provider = null)
    {
        $path   = StringUtils::replaceFirst("/", "/".$this->group_path."/", $path);
        $path   = str_replace("//", "/", $path);
        $paths  = explode("/", $path);

        $paths      = explode("/", $path);
        $function   = StringUtils::startWith($paths[0], "/") ? $path : array_shift($paths);
        $path       = implode("/", $paths);

        $this->_routing_dump[] = [
            "METHOD"        => $method,        
            "PATH"          => $path,
            "OBJECT"        => $obj instanceof Closure ? "Closure" : $obj,
            "CALL_FUNCTION" => $function,
            "PROVIDER"      => $provider,
        ];
    }

    public function dump()
    {
        //ksort($this->_routing_dump);
        return $this->_routing_dump;
    }

    /* Implement function from IRouter */
    // Http Restful Type
    public function get($path, $obj, CredentialProvider $provider = null)
    {
        // dd($_SERVER['REDIRECT_QUERY_STRING']);
        $this->pushDumb("GET", $path, $obj, $provider);
        
        if ($_SERVER['REQUEST_METHOD'] != "GET") return;

        $this->_route($path, $obj, $provider);
    }

    public function post($path, $obj, CredentialProvider $provider = null)
    {
        $this->pushDumb("POST", $path, $obj, $provider);

        if ($_SERVER['REQUEST_METHOD'] != "POST") return;

        $this->_route($path, $obj, $provider);
    }

    public function delete($path, $obj, CredentialProvider $provider = null)
    {
        $this->pushDumb("DELETE", $path, $obj, $provider);

        if ($_SERVER['REQUEST_METHOD'] != "DELETE") return;

        $this->_route($path, $obj, $provider);
    }

    public function patch($path, $obj, CredentialProvider $provider = null)
    {
        $this->pushDumb("PATCH", $path, $obj, $provider);

        if ($_SERVER['REQUEST_METHOD'] != "PATCH") return;

        $this->_route($path, $obj, $provider);
    }

    public function put($path, $obj, CredentialProvider $provider = null)
    {
        $this->pushDumb("PUT", $path, $obj, $provider);

        if ($_SERVER['REQUEST_METHOD'] != "PUT") return;

        $this->_route($path, $obj, $provider);
    }

    public function option($path, $obj, CredentialProvider $provider = null)
    {
        $this->pushDumb("OPTION", $path, $obj, $provider);

        if ($_SERVER['REQUEST_METHOD'] != "OPTION") return;

        $this->_route($path, $obj, $provider);
    }

    public function call($path, $obj, CredentialProvider $provider = null)
    {
        $this->pushDumb("*", $path, $obj, $provider);

        $this->_route($path, $obj, $provider);
    }

    // Router Functions
    public function group($path, $function)
    {
        // $this->group_path =  ltrim($path, '/');
        $this->group_path =  StringUtils::replaceFirst("/", "", $path);
     
        $function($this->_api);
        $this->group_path = "";
    }

    public function route($path, $obj, $routing, CredentialProvider $provider = null)
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
                    case "*":
                        $this->call($path, $obj, $provider);
                    break;
                    case "GET":
                        $this->get($path, $obj, $provider);
                    break;
                    case "POST":
                        $this->post($path, $obj, $provider);
                    break;
                    case "PUT":
                        $this->put($path, $obj, $provider);
                    break;
                    case "PATCH":
                        $this->patch($path, $obj, $provider);
                    break;
                    case "DELETE":
                        $this->delete($path, $obj, $provider);
                    break;
                    case "OPTION":
                        $this->option($path, $obj, $provider);
                    break;
                }
            }
        }
    }

    private function _route($path, $obj, CredentialProvider $provider = null)
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
        
        if ($this->group_path)
        {
            // dd("Yo");
            if (!StringUtils::startWith($query, $this->group_path)) return;
            $query = str_replace($this->group_path."/", "", $query);
        }
           
      
        // $query  = str_replace("args=", "", $_SERVER['REDIRECT_QUERY_STRING']);
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

        $func_name = empty($paths[0]) ? array_shift($paths) : array_shift($paths);

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

        $credential = null;
        if ($provider)
        {
            $provider->verify($this->_api);
            $credential = $provider->getCredential();
        }

        if ($obj instanceof ApiController)
        {
            $obj->setCredential($credential);
            $obj->onLoad($this->_api, $credential);
        }
        
            
        if ($func_name)
            call_user_func_array(array($obj, $func_name), $payload); 
        else
            call_user_func_array($obj, $payload);
 
        
    }


}
?>