<?php 
namespace Goosapi\Core\Interfaces;

interface IResponse
{
    public function submit($data, $code = 200);
}
?>