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
        parent::__construct(
            [
            "title",
            ]
        );
    }

    /**
     * Get all tags
     *
     * @return Tag[]
     * */
    public static function all()
    {
        self::setClassAndTable();
        return parent::getAllByTableName();
    }

    /**
     * Find tag by ID
     *
     * @param $keyValueArr array
     *
     * @return Tag
     */
    public static function find($keyValueArr): Tag
    {
        self::setClassAndTable();
        return parent::findOneByKey($keyValueArr);
    }


    /**
     * Save tag
     *
     * @return void
     */
    public function save()
    {
        self::setClassAndTable();
        parent::saveModel();
    }

    // Relationships =============================


}