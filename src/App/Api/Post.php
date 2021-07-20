<?php 
namespace API;

use Goosapi\goosapi;

class Post
{
    public function show($api, $id, $title)
    {
        $api->response()->submit([
            "success"   => "true",
            "message"   => "$id $title",
        ]);
        dd("Show $id $title");
    }

    public function list(goosapi $api, $id)
    {
        // d($id);
        // d("LIST HEREEE");
        // d($api);

        $api->response()->submit([
            "success"   => "true",
            "data"      => [
                "id"    => $id
            ]
        ]);
    }
   
}
?>