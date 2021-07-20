<?php namespace Api\JustRead;

use Goosapi\Core\ApiController;
use Goosapi\Database\DB;
use Goosapi\goosapi;
use Goosapi\Utils\ArrayUtils;

class Novel extends ApiController
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
        // dd("list");
       
        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");
    
        $perm_novels = 
                $this->db->query("CALL get_permission_novels('$user_id')")
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
                            "name"  => "Supanut Toy"
                        ],
                    ],
                    "community_link"    => "https://www.facebook.com/toy.mastersonic",
                    "use_name_mark"     => $data->use_name_mark ? true : false,
                    "updated_at"        => $data->chapter_updated_at,
                    "last_updated"      => strtotime($data->chapter_updated_at) ,
                    "chapters"          => [],
                ];
                $novels[$data->novel_id - 1] = $novel;
            }

            if ($data->chapter_id)
            {
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
            
        }

        $api->response()->submit([
            "success"   => true,
            "data"      => [
                "novels"    => array_values($novels),
            ]
        ]);
    }
    private function EncryptData($source, $pub_key)
    {
        $pub_key = "-----BEGIN PUBLIC KEY-----\r\n".$pub_key."\r\n-----END PUBLIC KEY-----";
        $key_resource = openssl_get_publickey($pub_key);
      
        openssl_public_encrypt(str_split($source, 200)[0],$crypttext, $key_resource);
        /*uses the already existing key resource*/
        
        return(base64_encode($crypttext));
    }

    

    public function read(goosapi $api, $id)
    {
        // dd("Read : $id");
        $chapter_id = $id;
        $user_id    = ArrayUtils::getValue($this->getCredential(), "user_id");
    

        $chapter_content = 
        $this->db->query("CALL get_permission_content('$user_id', '$chapter_id')")
            ->execute()
            ->first();

        
        
        if ($chapter_content)
        {
            $content = $chapter_content->content;
          
            $content = $this->EncryptData($content, 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtixUGzGpLXgZ7AV1HfmIHV/FEF+fww77FekRc2oLhUOd4HitwCPo76fjtdsQBEt8w9HZ3CXVphaAU2BA6MEZJ3ShVMsdAXb2ZA1C+lu7k1GV9M/BhucTg35HujSK647Sc5MwVLwFsN80dAnGsZF8gwb2TNUzXHwzbAb30T01zuqf8RCM75OwKZFYqzu7FOVrtk/w9mh92MOXG0l7WSqNIctu8Kxka/tEJJIA5nqMGNMocjwprXy66NS7FFy1GY+NnxfFLtODqq0tllc50UCDsnqSvNmj2wcnAcsCzNOoxPPgp7t8S+sQvOzgc5W3CDjIsYEiGD+vzSVNkGiRou577wIDAQAB');
            $api->response()->submit([
                "success"   => true,
                "data"      => [
                    "chapter_id"    => $chapter_content->chapter_id,
                    "content"       => $content ? $content : base64_encode("You cannot read it !!! :$user_id")
                ]
            ]);
        }
        else
        {
            $api->response()->submit([
                "success"   => false,
                "message"   => "No Permission",
                "errorno"   => 20001
            ]);
        }
    }
}