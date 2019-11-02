<?php

require_once "Model.php";
require_once "Post.php";

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

    public function name() {
        return $this->first_name . " " . $this->last_name;
    }

    // CRUD methods
    public function save()
    {
        // TODO: revamp email to id (duh)
        // Check if user already exists
        $foundUser = isset($this->oldEmail) ? self::findByEmail($this->oldEmail) : self::findByEmail($this->email);
        self::setClassAndTable();

        if($foundUser && isset($this->oldEmail)) {

            // Update with old email
            $oldEmail = $this->oldEmail;
            unset($this->oldEmail);

            parent::updateModel([
                "email" => $oldEmail
            ]);
            return true;
        } else if ($foundUser) {

            parent::updateModel([
                "email" => $this->email
            ]);
            return true;
        } else {
            // Fresh save

            // Encrypt the password
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);

            parent::saveModel();
            return true;
        }
    }

    // Relationships

    /**
     * @return Post[]
     * */
    public function posts(): array
    {
        return Post::findAllByKey(["user_id" => $this->id]);
    }

    /**
     * @return Notification[]
     * */
    public function notifications(): array
    {
        parent::setCustomClassAndTable("Notification", "user_notifications");
        // Get all notifications relating to the user
        $notifications = parent::findAllByKey(["user_id_to" => $this->id]);
        return $notifications;
    }
}