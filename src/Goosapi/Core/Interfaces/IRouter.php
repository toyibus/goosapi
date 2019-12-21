<?php 
namespace Goosapi\Core\Interfaces;

use Goosapi\Core\ApiController;

interface IRouter
{
    // Http Restful Type
    public function get     ($path, $obj);
    public function post    ($path, $obj);
    public function delete  ($path, $obj);
    public function patch   ($path, $obj);
    public function put     ($path, $obj);
    public function option  ($path, $obj);

    // Router Functions
    public function group   ($path, $function);
    public function route   ($path, $obj, $routing);
}
?>