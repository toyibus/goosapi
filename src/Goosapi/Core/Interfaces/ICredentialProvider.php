<?php 
namespace Goosapi\Core\Interfaces;

use Goosapi\goosapi;

interface ICredentialProvider
{
    public function verify(goosapi $api);
    public function setCredential($key, $value);
    public function getCredential();
}
?>