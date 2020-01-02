<?php namespace Goosapi\Database;

use Exception;
use PDO;

class DB
{
    private $_pdo;
    private $_pdo_statement; 

    public function __construct($conString, $user, $pass)
    {
        $this->_pdo = new PDO($conString, $user, $pass);
    }

    public function query($sql)
    {
        $this->_pdo_statement = $this->_pdo->prepare($sql);
        // d($sql);
        // d($this->_pdo_statement);
        return $this;
    }

    public function call($function, $params = [])
    {

    }

    public function execute($params = [])
    {
        $params_text = json_encode($params);
        // d("execute(\"$params_text\")");

        if (count($params) > 0)
            $this->_pdo_statement->execute($params);
        else 
            $this->_pdo_statement->execute();

        
        if ($this->_pdo_statement->errorInfo()[1])
        {
            $error = json_encode($this->_pdo_statement->errorInfo());
            // d($error);
            throw new Exception($error);
        }
        
        
        
        return $this;
    }

    public function fetch($type = PDO::FETCH_ASSOC)
    {
        return $this->_pdo_statement->fetch($type);
    }

    public function first()
    {
        return json_decode(json_encode($this->fetch()));
    }

    public function last()
    {
        $model = $this->all();
        return $model ? $model[count($model)-1] : false;
    }

    public function all()
    {
        $result = [];
        //dd(self::$__instance->_pdo->fetch());
        while($rs = $this->fetch())
        {
            $result[] = json_decode(json_encode($rs)); 
        }

        return $result;
    }

    public function getLastInsertId()
    {
        return $this->_pdo->lastInsertId();
    }
}