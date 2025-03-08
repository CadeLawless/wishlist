<?php

namespace Core;

use mysqli;

abstract class Model
{
    protected mysqli $db;

    public function __construct()
    {
        $config = require __DIR__ . '/../App/Config/database.php';
        $this->db = new mysqli($config['host'], $config['user'], $config['password'], $config['dbname']);
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function select(string $query, array $values=[]): array
    {
        if($selectStatement = $this->db->prepare($query)){
            $selectStatement->execute($values);
            return $selectStatement->get_result()->fetch_all(MYSQLI_BOTH);
        }else{
            //echo $db->error;
            return [];
        }
    }

    public function write(string $query, array $values): bool
    {
        if($writeStatement = $this->db->prepare($query)){
            if($writeStatement->execute($values)){
                return true;
            }else{
                //echo $writeStatement->error;
                return false;
            }
        }else{
            return false;
        }
    }

    public function getLastInsertID(): int{
        return $this->db->insert_id;
    }
}

?>