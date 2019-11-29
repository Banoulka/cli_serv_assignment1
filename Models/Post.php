<?php

require_once "Model.php";
require_once "User.php";
require_once "Tag.php";
require_once "Comparable.php";
require_once "Comment.php";

/**
 * @property-read int time
 * @property-read int id
 * @property int user_id
 * @property string title
 * @property string body
 * @property string description
 * @property string cover_image
 *
 */
class Post extends Model
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
     * @param int $limit
     * @return Post[]
     */
    public static function all($limit = -1)
    {
        $qb = QueryBuilder::getInstance()
            ->table("posts")
            ->orderby("time", QueryBuilder::ORDER_DESC)
            ->fetchAs("Post");

        if ($limit > 0) {
            $qb->limit($limit);
        }

        return $qb->getAll();
    }

    public static function allByLikes() {
        $sql = "SELECT count(post_likes.user_id) as \"Likes\", posts.*
                FROM post_likes, posts
                WHERE post_id = id
                GROUP BY post_id
                ORDER BY count(post_likes.user_id) DESC;
                ";

        $stmt = self::db()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Post");
        $stmt->execute();

        return $stmt->fetchAll();
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

    public function destroy()
    {
        // Delete likes
        self::setCustomClassAndTable("", "post_likes");
        parent::deleteModel(["post_id" => $this->id]);

        // Delete comments
        self::setCustomClassAndTable("", "post_comments");
        parent::deleteModel(["post_id" => $this->id]);

        // Delete tags
        self::setCustomClassAndTable("", "post_tags");
        parent::deleteModel(["post_id" => $this->id]);

        // Delete post off of user watchlist
        self::setCustomClassAndTable("", "user_watchlist");
        parent::deleteModel(["post_id" => $this->id]);

        // Delete announcements
        self::setCustomClassAndTable("", "post_announcements");
        parent::deleteModel(["post_id" => $this->id]);

        // Delete self
        self::setClassAndTable();
        parent::deleteModel(["id" => $this->id]);
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
     *
     * @param Comparable $self
     * @param Comparable $other
     * @return bool
     * @throws Exception
     */
    public static function compareTo(Post $self, Post $other)
    {
        if ($other instanceof  Post) {
            return $other->time <=> $self->time;
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
            parent::updateModel(["id" => $this->id]);
        } else {
            $now = new DateTime();
            $this->time = $now->getTimestamp();
            parent::saveModel();
            $this->id = parent::getLastID();

            // Send notification to followers
            $followers = $this->user()->followers();
            foreach ($followers as $user) {
                $notif = new Notification();
                $notif->user_id_from = $this->user()->id;
                $notif->user_id_to = $user->id;
                $notif->type = Notification::UPLOAD_BY_FOLLOWED;
                $notif->link = "/posts/view.php?post_id=$this->id";
                $notif->save();
            }
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

    /**
     * @param $rows
     * @return Post[]|Post
     */
    public static function random($rows)
    {
        self::setClassAndTable();
        return parent::random($rows);
    }

    public function watchCount()
    {
        parent::setCustomClassAndTable("", "user_watchlist");
        return count(parent::findAllByKey(["post_id" => $this->id]));
    }

    public function likesCount()
    {
//        parent::setCustomClassAndTable("", "post_likes");
//        return count(parent::findAllByKey(["post_id" => $this->id]));
        return QueryBuilder::getInstance()
            ->table("post_likes")
            ->where("post_id", $this->id)
            ->count();
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
        $filterSearch = false;
        $titleSearch = false;

        if (!empty($getReq["search"]) ) $titleSearch = true;
        if (isset($getReq["filters"]) && !empty($getReq["filters"]) ) $filterSearch = true;

        // Add the filters back if their are none
        if (!$filterSearch) {
            $getReq["filters"] = array();
            array_push($getReq["filters"], "alpha");
            array_push($getReq["filters"], "beta");
            array_push($getReq["filters"], "concept");
            array_push($getReq["filters"], "released");
        }

        // Get the order by result
        $orderBy = $getReq["sort-by"];
        $filters = $getReq["filters"];
        $sql = "";

        $tableName = "";
        if ($orderBy == "likes")
            $tableName = "post_likes";
        else if ($orderBy == "watches")
            $tableName = "user_watchlist";
        else if ($orderBy == "comments")
            $tableName = "post_comments";

        // If the title is set
        if ($titleSearch) {
            // Perform filters and title search with subquery
            $titleSearch = $getReq["search"];

            // Add the title search
            // Order by title score
            // If order by likes & filter search

            if ($tableName != "") {
                // Is not newest
                $sql = "SELECT posts.*, COUNT($tableName.user_id) as Something
                        FROM (SELECT id, user_id, title, description, body, cover_image, time, type_stage FROM
                    (SELECT posts.*, MATCH(title, description) AGAINST ('$titleSearch' IN NATURAL LANGUAGE MODE) as score
                     FROM posts HAVING score > 1
                     ORDER BY score DESC) AS POST_TITLES WHERE ";

                // For each filter
                foreach ($filters as $filter) {
                    $sql .= " type_stage = '$filter' ";
                    $filter == end($filters) ?: $sql .= "OR ";
                }
                $sql .= "ORDER BY time DESC) as posts LEFT JOIN $tableName ON posts.id = $tableName.post_id GROUP BY posts.id ORDER BY Something DESC;";

            } else {
                // Order by newest
                $sql = "SELECT id, user_id, title, description, body, cover_image, time, type_stage FROM
                        (SELECT posts.*, MATCH(title, description) AGAINST ('$titleSearch' IN NATURAL LANGUAGE MODE) as score
                        FROM posts HAVING score > 1
                        ORDER BY score DESC) AS POST_TITLES";
                $sql .= " WHERE ";
                foreach ($filters as $filter) {
                    $sql .= " type_stage = '$filter' ";
                    $filter == end($filters) ?: $sql .= "OR ";
                }
                $sql .= "ORDER BY time DESC;";
            }

        } else if (!$titleSearch) {
            // Perform just filter search

            if ($tableName != "") {
                // Order by something
                $sql = "SELECT posts.*, COUNT($tableName.user_id) as Something
                        FROM (SELECT id, user_id, title, description, body, cover_image, time, type_stage FROM posts WHERE ";
                foreach ($filters as $filter) {
                    $sql .= " type_stage = '$filter' ";
                    $filter == end($filters) ?: $sql .= "OR ";
                }
                $sql .= "ORDER BY time DESC) as posts LEFT JOIN $tableName ON posts.id = $tableName.post_id GROUP BY posts.id ORDER BY Something DESC;";
            } else {
                // Order by time
                $sql = "SELECT * FROM posts WHERE ";
                foreach ($filters as $filter) {
                    $sql .= " type_stage = '$filter' ";
                    $filter == end($filters) ?: $sql .= "OR ";
                }
                $sql .= "ORDER BY time DESC";
            }

        }

        return self::db()->query($sql)->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Post");
    }

    public function popularity() {
        return $this->watchCount() + $this->likesCount() + $this->commentCount();
    }

    /**
     * @return Post[]
     */
    public static function trending()
    {
        $sql = "SELECT posts.*,
                   (COUNT(DISTINCT pl.user_id) +
                    COUNT(DISTINCT pc.user_id)) AS Popularity
                FROM posts
                    LEFT JOIN post_likes pl on posts.id = pl.post_id
                    LEFT JOIN post_comments pc on posts.id = pc.post_id
                GROUP BY 1
                ORDER BY Popularity DESC
                LIMIT 150";
        return self::db()->query($sql)->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, "Post");
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

    /**
     *
     * @return User[]
     * */
    public function watchers()
    {
        parent::setCustomClassAndTable("User", "user_watchlist");
        $userWatch = parent::findAllByKey(["post_id" => $this->id]);
        $users = [];
        foreach ($userWatch as $item) {
            array_push($users, User::find(["id" => $item->user_id]));
        }
        return $users;
    }

    /**
     *
     * @return Announcement[]
     * */
    public function announcements()
    {
        parent::setCustomClassAndTable("Announcement", "post_announcements");
        $announcements = parent::findAllByKey(["post_id" => $this->id]);
        usort($announcements, array("Announcement", "compareTo"));
        return $announcements;
    }
}