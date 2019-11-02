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

    public function getTimeSince() {
        $postDateTime = new DateTime();
        $postDateTime->setTimestamp($this->time);
        $nowDateTime = new DateTime();

        $difference = $nowDateTime->diff($postDateTime);
        if ($difference->y > 0) {
            // Print full date plus year
            return $postDateTime->format("j F Y \a\\t G:i");
        }
        if ($difference->d > 1) {
            // Print full date
            return $postDateTime->format("j F \a\\t G:i");

        } else if ($difference->d == 1) {
            // Print yesterday and time
            return "Yesterday at " . $postDateTime->format("H:i");

        } else if ($difference->h > 0) {
            // Print hours
            return $difference->h . " hr" . ($difference->h != 1 ? "s" : "");

        } else if ($difference->i > 0) {
            // Print minutes
            return $difference->i . " min" . ($difference->i != 1 ? "s" : "");

        } else if ($difference->s > 30) {
            // Print seconds
            return $difference->s . " sec";

        } else {
            // Print just now
            return "just now";
        }
    }

    // Relationships
    /**
     * @return User
     * */
    public function user_to(): User
    {
        parent::setCustomClassAndTable("User", "users");
        return parent::find(["id" => $this->user_id_to]);
    }

    /**
     * @return User
     * */
    public function user_from(): User
    {
        parent::setCustomClassAndTable("User", "users");
        return parent::find(["id" => $this->user_id_from]);
    }
}