<?php 
namespace Goosapi\Core;

use Goosapi\Core\Interfaces\IResponse;

class Response implements IResponse
{
    public function submit($data, $code = 200)
    {
        if (gettype($data) == "array" || gettype($data) == "object")
        {
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode($data);
        }
        else
        {
            echo $data;
        }
        
        // Terminal API Request
        exit;
    }

}
?>