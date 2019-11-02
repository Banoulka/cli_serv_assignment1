<?php

require_once "Model.php";
require_once "User.php";
require_once "Tag.php";
require_once "Comparable.php";

class Post extends Model implements Comparable {

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

    /**
     * Find all by key
     *
     * @return Post[]
     * */
    public static function all()
    {
        self::setClassAndTable();
        $postArr = parent::getAllByTableName();
        // Sort by time
        // TODO: implment other sort methods
        usort($postArr, array("Post", "compareTo"));
        return $postArr;
    }

    /**
     * Find by ID
     *
     * @param $keyValueArr
     * @return Post
     */
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

    /**
     * Custom function to compare to using the post time as a reference
     *
     * @param Comparable $self
     * @param Comparable $other
     * @return int
     * @throws Exception
     */
    public static function compareTo(Comparable $self, Comparable $other)
    {
        if ($other instanceof Post) {
            if ($self->time == $other->time ) {
                return 0;
            } else {
                return $self->time < $other->time ? 1 : -1;
            }
        } else {
            throw new Exception("compareTo - Cannot compare objects of different types");
        }
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

    // Relationships ============================

    /**
     * @return User
     * */
    public function user(): User
    {
        return User::find([
            "id" => $this->user_id,
        ]);
    }

    /**
     * @return Tag[]
     * */
    public function tags(): array
    {
        parent::setCustomClassAndTable("Tag", "post_tags");
        // Get all tags relating to the selected post from the pivot table
        $postTags = parent::findAllByKey(["post_id" => $this->id]);
        $tags = array();
        foreach ($postTags as $tag) {
            // Get the title of the selected tag from the database
            $tag->title = Tag::find(["id" => $tag->tag_id])->title;
            array_push($tags, $tag);
        }
        return $tags;
    }
}