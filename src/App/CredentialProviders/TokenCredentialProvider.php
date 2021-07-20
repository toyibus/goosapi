<?php namespace CredetialProviders;

use Goosapi\Core\CredentialProvider;
use Goosapi\Database\DB;
use Goosapi\goosapi;
use Goosapi\Utils\ArrayUtils;

class TokenCredentialProvider extends CredentialProvider
{
    public function verify(goosapi $api)
    {
        // dd(getBearerToken());

        $token = getBearerToken();
        $db = new DB("mysql:dbname=AnyNovel;host=127.0.0.1", "root", "");

        
        $sql = "CALL get_user_info_by_token(:token)";
       
        $data = $db->query($sql)
                ->execute([":token" => $token])
                ->first();

        if (!$data)
        {
            $api->response()->submit(
                [
                    "success"   => false,
                    "message"   => "Token Unauthorized."
                ]
            );
        }
       
        if (time() > strtotime($data->expires)) // expired
        {
            $api->response()->submit(
                [
                    "success"   => false,
                    "message"   => "Token Expired."   
                ]
            );
        }

        $this->setCredential("token",       $token);
        $this->setCredential("client_id",   $data->client_id);   
        $this->setCredential("user_id",     $data->user_id);   
    
        $database = [
            "connection_string" => "mysql:dbname=AnyNovel_Resources_Novels;host=127.0.0.1",
            "username"          => "root",
            "password"          => ""
        ];
        
        $this->setCredential("database", $database);
    }

}