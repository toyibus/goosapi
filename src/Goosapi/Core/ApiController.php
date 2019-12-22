<?php 
namespace Goosapi\Core;

use Goosapi\goosapi;

abstract class ApiController
{
    private $credential; 

    public abstract function onLoad(goosapi $api, $data);

    public function setCredential($credential)
    {
        $this->credential = $credential;
    }

    public function getCredential()
    {
        return $this->credential;
    }
}
?>