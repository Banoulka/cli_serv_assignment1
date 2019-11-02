<?php

require_once "Model.php";
require_once "User.php";
require_once "Comparable.php";

class Tag extends Model {

    // Set the class and table of the model
    protected static function setClassAndTable()
    {
        parent::$className = "Tag";
        parent::$tableName = "tags";
    }

    // Writable attributes to be pulled from the database
    public function __construct()
    {
        // Construct with attributes
        parent::__construct([
            "title",
        ]);
    }

    // Get all
    public static function all()
    {
        self::setClassAndTable();
        return parent::getAllByTableName();
    }

    // Find by id
    public static function find($keyValueArr)
    {
        self::setClassAndTable();
        return parent::findOneByKey($keyValueArr);
    }

    public function save()
    {
        self::setClassAndTable();
        parent::saveModel();
    }

    // Relationships

}