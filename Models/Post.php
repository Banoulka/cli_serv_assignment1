<?php

require_once "Model.php";
require_once "User.php";

class Post extends Model {

    const TYPE_ALPHA = "alpha", TYPE_BETA = "beta", TYPE_RELEASED = "released", TYPE_CONCEPT = "concept";

    // Set the class and table of the model
    protected static function setClassAndTable()
    {
        parent::$className = "Post";
        parent::$tableName = "posts";
    }

    // Writable attributes to be pulled from the database
    public function __construct()
    {
        // Construct with attributes
        parent::__construct([
            "user_id",
            "title",
            "description",
            "body",
            "cover_image",
            "time",
            "type_stage",
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
        $this->time = strtotime(date("Y-m-d H:i:s"));
        var_dump($this);
        parent::saveModel();
    }

    // Relationships
    /**
     * @return User
     * */
    public function user() {
        return User::find([
            "id" => $this->user_id,
        ]);
    }
}