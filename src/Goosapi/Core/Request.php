<?php 
namespace Goosapi\Core;

use Goosapi\Core\Interfaces\IRequest;

class Request implements IRequest
{
    public function get()
    {
        return $_GET;
    }

    public function post()
    {
        return $_POST;
    }

    public function payload()
    {
        return $_REQUEST;
    }

    public function files()
    {
        return $_FILES;
    }

    public function headers()
    {
        return getallheaders();
    }

    public function server()
    {
        return $_SERVER;
    }
}
?>