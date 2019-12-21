<?php 
namespace Goosapi\Core\Interfaces;

interface IRequest
{
    public function get();
    public function post();
    public function payload();
    public function server();
    public function headers();
    public function files();
}
?>