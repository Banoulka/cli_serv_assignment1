<?php

require_once "Model.php";
require_once "User.php";
require_once "Tag.php";
require_once "Comparable.php";
require_once "Comment.php";

/**
 * @property int time
 * @property int id
 */
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
            return $self->time < $other->time ? 1 : -1;
        } else {
            throw new Exception("Cannot compare objects of different types");
        }
    }

    /**
     *
     * @param Comparable $self
     * @param Comparable $other
     * @return bool
     * @throws Exception
     */
    public static function compareToLikes(Comparable $self, Comparable $other)
    {
        if ($other instanceof  Post) {
            return $self->likesCount() < $other->likesCount() ? 1 : -1;
        } else {
            throw new Exception("Cannot compare objects of different types");
        }
    }

    /**
     *
     * @param Comparable $self
     * @param Comparable $other
     * @return bool
     * @throws Exception
     */
    public static function compareToWatches(Comparable $self, Comparable $other)
    {
        if ($other instanceof  Post) {
            return $self->watchCount() < $other->watchCount() ? 1 : -1;
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
            if (isset($this->cover_image)) {
                $this->cover_image = "/uploads/profile_pictures/" . $this->cover_image;
            }
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
        if (!isset($this->id)) {
            $this->id = self::getLastID();
        }
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

    public function commentCount()
    {
        parent::setCustomClassAndTable("Comment", "post_comments");
        return count(parent::findAllByKey(["post_id" => $this->id]));
    }

    /**
     * @param $getReq
     * @return Post[]
     */
    public static function searchPosts($getReq)
    {
        $sql = "SELECT * from posts WHERE ";
        $searchString = explode(" ", $getReq["search"]);
        if (!empty($searchString[0])) {
            foreach ($searchString as $word) {
                $sql .= "title LIKE '%$word%'";
                if ($word != end($searchString)) {
                    $sql .= " OR ";
                }
            }
            $sql .= " OR ";
            foreach ($searchString as $word) {
                $sql .= "description LIKE '%$word%'";
                if ($word != end($searchString)) {
                    $sql .= " OR ";
                }
            }

            $sql .= " AND ";
        }
        $sql .= " (";
        foreach ($getReq["filters"] as $filter) {
            $sql .= "type_stage LIKE '%$filter%'";
            if ($filter != end($getReq["filters"])) {
                $sql .= " OR ";
            }
        }
        $sql .= ") ORDER BY title";

        Post::setCustomClassAndTable("Post", "posts");
        $posts = parent::query($sql);
        $tagsToSearch = $getReq["tags"];
        // Tags
        for ($i = 0; $i < count($posts)-1; $i++) {
            $post = $posts[$i];
            if ($post instanceof Post) {
                $tags = $post->tags();
                $tagFound = false;
                foreach ($tags as $tag) {
                    if (in_array(strtolower($tag->title), $tagsToSearch)) {
                        $tagFound = true;
                        break;
                    }
                }
                if (!$tagFound) {
                    unset($posts[$i]);
                    $posts = array_values($posts);
                }
            }
        }
        return $posts;
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
    public function tags()
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


    /**
     *
     * @return Comment[]
     * */
    public function comments()
    {
        parent::setCustomClassAndTable("Comment", "post_comments");
        $comments = parent::findAllByKey(["post_id" => $this->id]);
        usort($comments, array("Comment", "compareTo"));
        return $comments;
    }
}