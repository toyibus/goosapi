<?php namespace Api\JustWrite;

use Exception;
use Goosapi\Core\ApiController;
use Goosapi\Database\DB;
use Goosapi\goosapi;
use Goosapi\Utils\ArrayUtils;

class NovelPermissions extends ApiController
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

    public function getList(goosapi $api, $id)
    {
        // dd("get permission list : $id");
        $novel_id   = $id;
      
        $sql = "SELECT user_id AS id FROM novel_permissions WHERE novel_id = $novel_id GROUP BY user_id ";
        $data = $this->db->query($sql)->execute()->all();
   
        $api->response()->submit($data);
    }

    public function create(goosapi $api, $id, $user_id)
    {
        // dd("Create novel permission $id, $user_id");

        $sql = "SELECT episode_no FROM chapters ";
        $sql.= "WHERE novel_id = $id ";
        $sql.= "GROUP BY episode_no ";
        $episode_data = $this->db->query($sql)->execute()->all();
           
        // var_dump($episode_data);
        foreach ($episode_data as $e)
        {
            $episode_no = $e->episode_no;
            $sql = "INSERT INTO novel_permissions ";
            $sql.= "(user_id, novel_id, episode_no) ";
            $sql.= "VALUES ";
            $sql.= "(:user_id, :novel_id, :episode_no) ";

            try
            {
                $this->db->query($sql)
                    ->execute([
                        ":user_id"      => $user_id,
                        ":novel_id"     => $id,
                        ":episode_no"   => $episode_no
                    ]);
        
            }
            catch (Exception $ex)
            {
                // Log something
            }
           
        }

        $api->response()->submit([
            "success" => true,
            "message" => "created success.",
        ]);
    }

    public function delete(goosapi $api, $novel_id, $user_id)
    {
        // dd("delete novel permission $novel_id, $user_id");

        $this->db->query("DELETE FROM novel_permissions WHERE novel_id = $novel_id AND user_id = '$user_id'")
            ->execute();
        
        $api->response()->submit([
            "success" => true,
            "message" => "created success.",
        ]);

    }
}