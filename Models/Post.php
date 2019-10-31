<?php

require_once "Model.php";
require_once "User.php";
require_once "Tag.php";

class Post extends Model {

    const TYPE_ALPHA = "alpha", TYPE_BETA = "beta", TYPE_RELEASED = "released", TYPE_CONCEPT = "concept";

    // Set the class and table of the model
    protected static function setClassAndTable()
    {
        parent::$className = "Post";
        parent::$tableName = "posts";
    }

    protected static function setCustomClassAndTable($className, $tableName)
    {
        parent::$className = $className;
        parent::$tableName = $tableName;
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
        $now = new DateTime();
        $this->time = $now->getTimestamp();
        parent::saveModel();
    }

    public function getTimeSince() {
        $postDateTime = new DateTime();
        $postDateTime->setTimestamp($this->time);
        $nowDateTime = new DateTime();

        $difference = $nowDateTime->diff($postDateTime);
        if ($difference->y > 0) {
            // Print full date plus year
            return $postDateTime->format("d F Y \a\\t G:i");
        }
        if ($difference->d > 1) {
            // Print full date
            return $postDateTime->format("d F \a\\t G:i");

        } else if ($difference->d == 1) {
            // Print yesterday
            return "yesterday at " . $difference->h . ":" . $difference->i;

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
    public function user() {
        return User::find([
            "id" => $this->user_id,
        ]);
    }

    public function tags() {
        self::setCustomClassAndTable("Tag", "post_tags");
        $postTags = parent::findAllByKey(["post_id" => $this->id]);
        $tags = array();
        foreach ($postTags as $tag) {
            $tag->title = Tag::find(["id" => $tag->tag_id])->title;
            array_push($tags, $tag);
        }
        return $tags;
    }
}