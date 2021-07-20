<?php namespace Api\JustWrite;

use Goosapi\Core\ApiController;
use Goosapi\Database\DB;
use Goosapi\goosapi;
use Goosapi\Utils\ArrayUtils;

class Novels extends ApiController
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

    public function getList(goosapi $api)
    {
        // dd("Novel Get List");
        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");
      
        $statement = "SELECT * FROM view_novel_chapter WHERE owner_user_id = :id";
        $data = 
            $this->db->query($statement)
                ->execute([":id" => $user_id])
                ->all();

        $novels = [];
        foreach ($data as $item)
        {
            if (empty($novels[$item->novel_id - 1]))
            {
                $novel = [
                    "id"                => $item->novel_id, 
                    "title"             => $item->novel_title, 
                    "description"       => $item->novel_description,
                    "cover_image_url"   => $item->cover_image_url,
                    "episodes"          => [],
                ];
                $novels[$item->novel_id - 1] = $novel;
            }

            if (empty($novel[$item->novel_id - 1]["episodes"][$item->episode_no]))
            {
                $episode = [
                    "id"        => $item->episode_no,
                    "title"     => "Episode " . $item->episode_no,
                    "chapters"  => [],
                ];

                $novels[$item->novel_id - 1]["episodes"][$item->episode_no - 1] = $episode;
            }

            if ($item->chapter_id)
            {
                $chapter = [
                    "title"         => $item->chapter_title,
                    "last_updated"  => strtotime($item->chapter_updated_at)
                ];
    
                $novels
                    [$item->novel_id - 1]
                    ["episodes"]
                    [$item->episode_no - 1]
                    ["chapters"][] = $chapter;
            }
        }

        foreach ($novels as $index => $novel)
        {
            $novels[$index]["episodes"] = array_values($novel["episodes"]);
        }

        $api->response()->submit(array_values($novels));
    }

    public function getOne(goosapi $api, $id)
    {
        // dd("Novel Get : $id");
        
        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");
       
        $novel_id   = $id;

        $statement = "SELECT * FROM view_novel_chapter WHERE owner_user_id = :id AND novel_id = :novel_id";
        $data = 
            $this->db->query($statement)
                ->execute([
                    ":id"       => $user_id,
                    ":novel_id" => $novel_id,
                ])
                ->all();

        $novels = [];
        foreach ($data as $item)
        {
            if (empty($novels[$item->novel_id - 1]))
            {
                $novel = [
                    "id"                => $item->novel_id, 
                    "title"             => $item->novel_title, 
                    "description"       => $item->novel_description,
                    "cover_image_url"   => $item->cover_image_url,
                    "episodes"          => [],
                ];
                $novels[$item->novel_id - 1] = $novel;
            }

            if (empty($novel[$item->novel_id - 1]["episodes"][$item->episode_no]))
            {
                $episode = [
                    "id"        => $item->episode_no,
                    "title"     => "Episode " . $item->episode_no,
                    "chapters"  => [],
                ];

                $novels[$item->novel_id - 1]["episodes"][$item->episode_no - 1] = $episode;
            }

            if ($item->chapter_id)
            {
                $chapter = [
                    "title"         => $item->chapter_title,
                    "last_updated"  => strtotime($item->chapter_updated_at)
                ];
    
                $novels
                    [$item->novel_id - 1]
                    ["episodes"]
                    [$item->episode_no - 1]
                    ["chapters"][] = $chapter;
            }
        }

        foreach ($novels as $index => $novel)
        {
            $novels[$index]["episodes"] = array_values($novel["episodes"]);
        }

        $api->response()->submit(
            count($novels) ? array_values($novels)[0] : null
        );
    }

    public function create(goosapi $api)
    {
        // dd("Create Novels");
        // dd($api->request()->payload());
        $payload = $api->request()->payload();

        $title          = ArrayUtils::getValue($payload, "title");
        $description    = ArrayUtils::getValue($payload, "description");
        $use_name_mark  = ArrayUtils::getValue($payload, "use_name_mark", 0);

        $required = [
            "title"          => $title,
            "description"   => $description,
        ];

        ArrayUtils::findEmptyValue($required, function ($key) use ($api) {
            $api->response()->submit([
                "success" => false,
                "message" => "`$key` required.",
            ]);
        });

        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");
       
        $sql  = "INSERT INTO novels ";
        $sql .= "(title, description, use_name_mark, owner_user_id, cover_image_url) ";
        $sql .= "VALUES ";
        $sql .= "(:title, :description, :use_name_mark, :user_id, '') ";

    
        $this->db->query($sql)
            ->execute([
                ":title"            => $title,
                ":description"      => $description,
                ":use_name_mark"    => $use_name_mark,
                ":user_id"          => $user_id,
            ]);

        $api->response()->submit([
            "success" => true,
            "message" => "created success.",
        ]);
    }

    public function update(goosapi $api, $id)
    {
        // dd("Update Novel : $id");

        $payload = $api->request()->payload();

        $title          = ArrayUtils::getValue($payload, "title");
        $description    = ArrayUtils::getValue($payload, "description");
        $use_name_mark  = ArrayUtils::getValue($payload, "use_name_mark", 0);
        $novel_id       = $id; 

        $required = [
            "title"          => $title,
            "description"   => $description,
        ];

        ArrayUtils::findEmptyValue($required, function ($key) use ($api) {
            $api->response()->submit([
                "success" => false,
                "message" => "`$key` required.",
            ]);
        });

        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");

        $sql = "UPDATE novels SET ";
        $sql.= "title = :title, ";
        $sql.= "description = :description, ";
        $sql.= "use_name_mark = :use_name_mark ";
        $sql.= "WHERE owner_user_id = :user_id ";
        $sql.= "AND novel_id = :novel_id ";
        
        $this->db->query($sql)
            ->execute([
                ":title"            => $title,
                ":description"      => $description,
                ":use_name_mark"    => $use_name_mark,
                ":user_id"          => $user_id,
                ":novel_id"         => $novel_id,
            ]);

        $api->response()->submit([
            "success" => true,
            "message" => "updated success.",
        ]);
    }

    public function delete(goosapi $api, $id)
    {
        $novel_id   = $id; 

        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");
   
        $sql = "DELETE FROM novels ";
        $sql.= "WHERE novel_id = :novel_id ";
        $sql.= "AND owner_user_id = :user_id ";
      
        
        $this->db->query($sql)
            ->execute([
                ":user_id"          => $user_id,
                ":novel_id"         => $novel_id,
            ]);

        $api->response()->submit([
            "success" => true,
            "message" => "deleted success.",
        ]);
    }
}