<?php namespace Api\JustWrite;

use Goosapi\Core\ApiController;
use Goosapi\Database\DB;
use Goosapi\goosapi;
use Goosapi\Utils\ArrayUtils;

class Chapters extends ApiController
{
    private $db; 

    public function onLoad(goosapi $api, $credential)
    {
        $database   = ArrayUtils::getValue($credential, "database");
        $client_id  = ArrayUtils::getValue($credential, "client_id");
        $conString  = ArrayUtils::getValue($database, "connection_string");
        $dbUser     = ArrayUtils::getValue($database, "username");
        $dbPass     = ArrayUtils::getValue($database, "password");

        $required = [
            "[credential] client_id"        => $client_id,
            "[database] connection_string"  => $conString,
            "[database] username"           => $dbUser,
        ];

        ArrayUtils::findEmptyValue($required, function($key) use ($api) {
            $api->response()->submit([
                "success"   => false,
                "message"   => "`$key` required.",
            ]);
        });

        $this->db = new DB($conString, $dbUser, $dbPass);
    }

    public function getOne(goosapi $api, $novel_id, $episode_no, $id)
    {
        dd("Get Chapter : $novel_id, $episode_no, $id");

       
        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");
       
        $sql = "SELECT ";
        $sql.= "novel_id AS id, ";
        $sql.= "title, description, cover_image_url, ";
        $sql.= "created_at, updated_at ";
        $sql.= "FROM novels ";
       
        
    }

    public function create()
    {
        dd("create");
    }

    public function update()
    {
        dd("update");
    }

    public function delete()
    {
        dd("delete");
    }
}