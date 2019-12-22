<?php namespace Goosapi\Core;

use Goosapi\Core\Interfaces\ICredentialProvider;
use Goosapi\goosapi;

abstract class CredentialProvider implements ICredentialProvider
{
    private $credential;

    public function __construct()
    {
        $this->credential = [];    
    }

    public function setCredential($key, $value)
    {
        $this->credential[$key] = $value;
    }

    public function getCredential()
    {
        return $this->credential;
    }
}