<?php namespace Api\Auth;

use Exception;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Goosapi\Core\ApiController;
use Goosapi\Database\DB;
use Goosapi\goosapi;
use Goosapi\Utils\ArrayUtils;

class Token extends ApiController
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

    public function token(goosapi $api)
    {  
        $payload    = $api->request()->payload();

        $grant_type = ArrayUtils::getValue($payload, "grant_type");
        $fb_token   = ArrayUtils::getValue($payload, "as_fb_token");
        $pubk       = ArrayUtils::getValue($payload, "pubk");
        $client_id  = ArrayUtils::getValue($this->getCredential(), "client_id");

        
        $required = [
            "grant_type"            => $grant_type,
            "as_fb_token"           => $fb_token,
            "pubk"                  => $pubk
        ];

        ArrayUtils::findEmptyValue($required, function($key) use ($api) {
            $api->response()->submit([
                "success"   => false,
                "message"   => "`$key` required.",
            ]);
        });

        $fb = new Facebook([
            'app_id' => '1455602474588746',
            'app_secret' => '653ebb17160a1ef45e20e80c6e98b565',
            'default_graph_version' => 'v5.0',
        ]);


        try 
        {
            
            $response = $fb->get('/me?fields=id,name', "$fb_token");
            $facebook_user = $response->getGraphUser();

            if ($facebook_user)
            {
                $facebook_id    = $facebook_user["id"];
                $facebook_name  = $facebook_user["name"];
                // dd($facebook_name);

                // check user in database
                $sql = "SELECT * FROM oauth_users ";
                $sql.= "WHERE username = :fid ";
                $user = $this->db->query($sql)
                        ->execute([":fid" => $facebook_id])
                        ->first();
  
                if(!$user)
                {
                    // dd("test");
                    $sql = "INSERT INTO oauth_users ";
                    $sql.= "(username, name, user_type) ";
                    $sql.= "VALUES ";
                    $sql.= "(:fid, :name, 'facebook') ";
                    $this->db->query($sql)->execute();

                    $sql = "SELECT * FROM oauth_users ";
                    $sql.= "WHERE username = :fid ";
                    $user = $this->db->query($sql)
                            ->execute([":fid" => $facebook_id])
                            ->first();  
                }
             
                // Generate Token 
                $token = bin2hex(random_bytes(32));

                // Push Token in storage   
                $token_timeout_sec = 500000;
                $sql = "INSERT INTO oauth_access_tokens ";
                $sql.= "(access_token, client_id, user_id, scope, pub_key, expires) ";
                $sql.= "VALUES ";
                $sql.= "(:token, :cid, :uid, 'xxx', :pubk, TIMESTAMPADD(SECOND, $token_timeout_sec, CURRENT_TIMESTAMP())) ";
                
                $this->db->query($sql)
                    ->execute([
                        ":token"    => $token,
                        ":cid"      => $client_id,
                        ":uid"      => $user->username,
                        ":pubk"     => $pubk,  
                    ]);

                $sql = "SELECT access_token AS `value`, expires ";
                $sql.= "FROM oauth_access_tokens ";
                $sql.= "WHERE access_token = :token ";
                $tokenData = 
                    $this->db->query($sql)
                        ->execute([":token" => $token])->first();
                

                $api->response()->submit([
                    "success"   => true,
                    "data"      => [
                        "access_token"  => $tokenData,  
                        "token_type"    => "bearer",
                        // "scope"         => "read",
                        // "refresh_token" => $tokenData,
                        "info"          => [
                            "user_type"     => "facebook",
                            "name"          => "$facebook_name",
                            "facebook_id"   => "$facebook_id",
                            "image_url"     => "http://graph.facebook.com/$facebook_id/picture"
                        ]
                    ]
                ]);
                
            }
            else
            {
                $api->response()->submit([
                    "success"   => false,
                    "data"      => [
                        "message"   => "Invalid Facebook token."
                    ]
                ]);
            }
          
        } 
        catch(FacebookResponseException $e) 
        {
            //echo 'Graph returned an error: ' . $e->getMessage();
            $api->response()->submit(
                [
                    "success"   => false,
                    "message"   => 'Graph returned an error: ' . $e->getMessage(),
                    
                ]
            );
        } 
        catch(FacebookSDKException $e) 
        {
            $api->response()->submit(
                [
                    "success"   => false,
                    "message"   => 'Facebook SDK returned an error: ' . $e->getMessage(),   
                ]
            );
        }
        catch(Exception $ex)
        {
            $api->response()->submit(
                [
                    "success"   => false,
                    "message"   => "Exception Error. " . $ex->getMessage(),
                    
                ]
            );
        } 
        
       
    }
}