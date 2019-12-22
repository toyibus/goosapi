<?php 
namespace Goosapi\Core\Interfaces;

use Goosapi\Core\ApiController;
use Goosapi\Core\CredentialProvider;

interface IRouter
{
    // Http Restful Type
    public function get     ($path, $obj, CredentialProvider $provider = null);
    public function post    ($path, $obj, CredentialProvider $provider = null);
    public function delete  ($path, $obj, CredentialProvider $provider = null);
    public function patch   ($path, $obj, CredentialProvider $provider = null);
    public function put     ($path, $obj, CredentialProvider $provider = null);
    public function option  ($path, $obj, CredentialProvider $provider = null);
    
    // All method allows 
    public function call    ($path, $obj, CredentialProvider $provider = null);

    // Router Functions
    public function group   ($path, $function);
    public function route   ($path, $obj, $routing, CredentialProvider $provider = null);
}
?>