<?php namespace CredetialProviders;

use Goosapi\Core\CredentialProvider;
use Goosapi\goosapi;

class NoAuthCredentialProvider extends CredentialProvider
{
    public function verify(goosapi $api)
    {  
        // Supanut Panyagosa Facebook ID
        $this->setCredential("user_id", "3308507162555408");   
    
        $database = [
            "connection_string" => "mysql:dbname=AnyNovel_Resources_Novels;host=127.0.0.1",
            "username"          => "root",
            "password"          => ""
        ];
        
        $this->setCredential("database", $database);
    }
}