<?php namespace Api\JustRead;

use Exception;
use Goosapi\Core\ApiController;
use Goosapi\Database\DB;
use Goosapi\goosapi;
use Goosapi\Utils\ArrayUtils;

class FavoriteNovel extends ApiController
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

    public function list(goosapi $api)
    {
        // dd("Favorite Novel list()");
  
        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");
     
       
        $perm_novels = 
            $this->db->query("CALL get_favorite_novels('$user_id')")
                ->execute()
                ->all();
        
        $novels = [];
        foreach ($perm_novels as $countIndex => $data)
        {
            if (empty($novels[$data->novel_id - 1]))
            {
                $novel = [
                    "novel_id"          => $data->novel_id,
                    "title"             => $data->novel_title,
                    "description"       => $data->novel_description,
                    "cover_image_url"   => $data->cover_image_url,
                    "authors"           => [
                        [
                            "id"    => 1,
                            "name"  => "Supanut Toy",
                        ],
                    ],
                    "community_link"    => "https://www.facebook.com/toy.mastersonic",
                    "use_name_mark"     => $data->use_name_mark ? false : true,
                    "updated_at"        => $data->chapter_updated_at,
                    "last_updated"      => strtotime($data->chapter_updated_at) ,
                    "chapters"          => [],
                ];
                $novels[$data->novel_id - 1] = $novel;
            }

            $chapter = [
                "chapter_id"    => (int)$data->chapter_id,
                "chapter_no"    => (int)$countIndex + 1,
                "episode_no"    => (int)$data->episode_no,
                "title"         => $data->chapter_title,
                "last_updated"  => strtotime($data->chapter_updated_at) ,
                "updated_at"    => $data->chapter_updated_at,
                "can_read"     => (int)$data->available ? true : false
            ];

            if (strtotime($data->chapter_updated_at) > strtotime($novels[$data->novel_id - 1]["updated_at"]))
            {
                $novels[$data->novel_id - 1]["updated_at"] = $data->chapter_updated_at;
                $novels[$data->novel_id - 1]["last_updated"] = strtotime($data->chapter_updated_at);
            }
               

            $novels[$data->novel_id - 1]["chapters"][] = $chapter;
            $novels[$data->novel_id - 1]["number_of_chapters"] = count($novels[$data->novel_id - 1]["chapters"]);
        }

        $api->response()->submit([
            "success"   => true,
            "data"      => [
                "novels"    => $novels,
            ]
        ]);
    }

    public function add(goosapi $api, $id)
    {
        // dd("Add Favorite Novel");
        
        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");
       
        $novel_id = $id;
        
        try
        {
            $this->db->query("CALL add_favorite_novel('$user_id', '$novel_id')")
                ->execute();
        }
        catch (Exception $ex)
        {
            $api->response()->submit([
                "success" => false,
                "message" => $ex->getMessage(),
            ]);
        }
                
        $api->response()->submit([
            "success"   => true,
            "message"   => "Add favorite done"
        ]);
    }

    public function remove(goosapi $api, $id)
    {
        // dd("Remove Favorite Novel");
  
        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");

        $novel_id = $id;

        try
        {
            $this->db->query("CALL remove_favorite_novel('$user_id', '$novel_id')")
                ->execute();
        }
        catch (Exception $ex)
        {
            $api->response()->submit([
                "success" => false,
                "message" => $ex->getMessage(),
            ]);
        }
            
        $api->response()->submit([
            "success"   => true,
            "message"   => "Remove favorite done"
        ]);
    }
}