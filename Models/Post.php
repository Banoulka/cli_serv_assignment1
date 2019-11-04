<?php

require_once "Model.php";
require_once "User.php";
require_once "Tag.php";
require_once "Comparable.php";

class Post extends Model implements Comparable
{

    const TYPE_ALPHA = "alpha", TYPE_BETA = "beta", TYPE_RELEASED = "released", TYPE_CONCEPT = "concept";

    /**
     * Set the class and table of the model
     *
     * @return void;
     * */
    protected static function setClassAndTable()
    {
        parent::$className = "Post";
        parent::$tableName = "posts";
    }

    /**
     * Writable attributes to be pulled from the database
     * */
    public function __construct()
    {
        // Construct with attributes
        parent::__construct(
            [
            "user_id",
            "title",
            "description",
            "body",
            "cover_image",
            "time",
            "type_stage",
            ]
        );
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
     * @param $keyValueArr array
     *
     * @return Post
     */
    public static function find($keyValueArr)
    {
        self::setClassAndTable();
        return parent::findOneByKey($keyValueArr);
    }

    /**
     * Custom function to compare to using the post time as a reference
     *
     * @param Comparable $self  this object
     * @param Comparable $other object to compare to
     *
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
            throw new Exception("Cannot compare objects of different types");
        }
    }

    /**
     * Save the Post
     *
     * @throws Exception
     * @return void
     */
    public function save()
    {
        self::setClassAndTable();

        $foundPost = Post::find(["id" => isset($this->id) ? $this->id : "-1"] );
        if ($foundPost) {
            parent::updateModel(
                ["id" => $this->id],
            );
        } else {
            $now = new DateTime();
            $this->time = $now->getTimestamp();
            parent::saveModel();
        }
    }

    /**
     * Function to return the time since posted to a human readable
     * format
     *
     * @return string
     */
    public function getTimeSince()
    {
        return Helpers::getTimeSince($this->time);
    }

    public function addTags($tagsArr)
    {
        $this->id = self::getLastID();
        foreach ($tagsArr as $tag) {
            $tagID = Tag::find(["title" => $tag])->id;
            self::setCustomClassAndTable("Tag", "post_tags");
            parent::insert()->value("post_id", $this->id)->value("tag_id", $tagID)->executeInsert();
        }
    }

    public function watchCount()
    {
        parent::setCustomClassAndTable("", "user_watchlist");
        return count(parent::findAllByKey(["post_id" => $this->id]));
    }

    public function likesCount()
    {
        parent::setCustomClassAndTable("", "post_likes");
        return count(parent::findAllByKey(["post_id" => $this->id]));
    }

    // Relationships ============================

    /**
     * Method to get the owner of the post
     *
     * @return User
     * */
    public function user(): User
    {
        return User::find(["id" => $this->user_id]);
    }

    /**
     * Method to get all the tags of the post
     *
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