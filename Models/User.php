<?php

require_once "Model.php";

class User extends Model {

    // Set the class and table of the model
    protected static function setClassAndTable()
    {
        parent::$className = "User";
        parent::$tableName = "users";
    }

    // Writable attributes to be pulled from the database
    public function __construct()
    {
        // Construct with attributes
        parent::__construct([
            "first_name",
            "last_name",
            "display_name",
            "email",
            "display_pic",
            "bio",
            "password",
        ]);
    }

    // Get all users
    public static function all()
    {
        self::setClassAndTable();
        return parent::getAllByTableName();
    }

    // Find user by id
    public static function find($keyValueArr)
    {
        self::setClassAndTable();
        return parent::findOneByKey($keyValueArr);
    }

    public static function findByEmail($email) {
        self::setClassAndTable();
        return parent::findOneByKey([
            "email" => $email
        ]);
    }

    // CRUD methods
    public function save()
    {
        // Encrypt the password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        self::setClassAndTable();
        parent::saveModel();
        return true;
    }

    // Relationships

}