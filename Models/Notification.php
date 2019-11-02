<?php

require_once "Model.php";
require_once "User.php";
require_once "Tag.php";
require_once "Comparable.php";

class Notification extends Model
{

    // Set the class and table of the model
    protected static function setClassAndTable()
    {
        parent::$className = "Notification";
        parent::$tableName = "user_notifications";
    }

    // Writable attributes to be pulled from the database
    public function __construct()
    {
        // Construct with attributes
        parent::__construct([
            "user_id_from",
            "user_id_to",
            "type",
            "read",
        ]);
    }

    public static function find($keyValueArr)
    {
        // TODO: Implement find() method.
    }

    public function save()
    {
        self::setClassAndTable();
        $now = new DateTime();
        $this->time = $now->getTimestamp();
        parent::saveModel();
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    /**
     * Get all
     *
     * @return void
     */
    protected static function all()
    {
        // TODO: Implement all() method.
    }
}