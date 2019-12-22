<?php
namespace Goosapi;

use Goosapi\Core\CredentialProvider;
use Goosapi\Core\Interfaces\ICredentialProvider;
use Goosapi\Core\Interfaces\IRouter;
use Goosapi\Core\Request;
use Goosapi\Core\Response;
use Goosapi\Core\Router;

class goosapi implements IRouter
{
    private $router;
    private $credential;

    private $request;
    private $response;

    public function __construct()
    {
        $this->router   = new Router($this);
        $this->request  = new Request();
        $this->response = new Response();
        
    }

    public function allowCORs()
    {
        // ------------------ Allow CORs ----------------------

        if (isset($_SERVER['HTTP_ORIGIN'])) 
        {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Max-Age: 86400"); // cache for 1 day 
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') 
        {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        
            exit(0);
        }
        
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /* Implement function from IRouter */
    // Http Restful Type

    public function router()
    {
        return $this->router;
    }

    public function get($path, $obj, CredentialProvider $provider = null)
    {
        $this->router->get($path, $obj, $provider);
    }

    public function post($path, $obj, CredentialProvider $provider = null)
    {
        $this->router->post($path, $obj, $provider);
    }

    public function delete($path, $obj, CredentialProvider $provider = null)
    {
        $this->router->delete($path, $obj, $provider);
    }

    public function patch($path, $obj, CredentialProvider $provider = null)
    {
        $this->router->patch($path, $obj, $provider);
    }

    public function put($path, $obj, CredentialProvider $provider = null)
    {
        $this->router->put($path, $obj, $provider);
    }

    public function option($path, $obj, CredentialProvider $provider = null)
    {
        $this->router->option($path, $obj, $provider);
    }

    // Router Functions
    public function group($path, $function)
    {
        $this->router->group($path, $function);
    }

    public function route($path, $obj, $routing, CredentialProvider $provider = null)
    {
        // $this->credential = $credential;
        $this->router->route($path, $obj, $routing, $provider);
    }

    // public function verify(ICredentialProvider $provider, $function)
    // {
    //     $provider->verify($this);
    //     $function($provider->getData($this));
    // }

    // public function getCredential()
    // {
    //     return $this->credential;
    // }

    public function response()
    {
        return $this->response;
    }

    public function request() 
    {
        return $this->request;
    }

}

?>