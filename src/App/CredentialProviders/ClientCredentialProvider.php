<?php namespace CredetialProviders;

use Goosapi\Core\CredentialProvider;
use Goosapi\Database\DB;
use Goosapi\goosapi;
use Goosapi\Utils\ArrayUtils;

class ClientCredentialProvider extends CredentialProvider
{
    public function verify(goosapi $api)
    {

        $payload = $api->request()->server();

        $client_id      = ArrayUtils::getValue($payload, "PHP_AUTH_USER");
        $client_secret  = ArrayUtils::getValue($payload, "PHP_AUTH_USER");
        
        $validator = [
            "client_id"     => $client_id,
            "client_secret" => $client_secret
        ];

        ArrayUtils::findEmptyValue($validator, function($key) use ($api) {
            $api->response()->submit([
                "success"   => false,
                "message"   => "`$key` Required."
            ]);
        });
       
        // d("verify here!!!");
        $sql = "SELECT * FROM oauth_clients ";
        $sql.= "WHERE client_id = :client_id ";
        $sql.= "AND client_secret = :client_secret ";

        $client_id      = $_SERVER["PHP_AUTH_USER"];
        $client_secret  = $_SERVER["PHP_AUTH_PW"];

        $db = new DB("mysql:dbname=AnyNovel;host=127.0.0.1", "root", "");
        $client = $db
            ->query($sql)
            ->execute([
                ":client_id"        => $client_id,
                ":client_secret"    => $client_secret,
            ])
            ->first();
        
        if (!$client)
            $api->response()->submit([
                "success"   => false,
                "data"      => [
                    "message"   => "Invalid."
                ]
            ]);

        $database = [
            "connection_string" => "mysql:dbname=AnyNovel;host=127.0.0.1",
            "username"          => "root",
            "password"          => ""
        ];
        
        $this->setCredential("client_id", $client->client_id);
        $this->setCredential("database", $database);
       
    }

}