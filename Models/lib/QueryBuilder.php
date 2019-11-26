<?php

require_once "../Database.php";

class QueryBuilder
{
    private static function db()
    {
        return Database::getInstance()->getdbConnection();
    }

    private static function query($className, $tableName)
    {
        
    }
}