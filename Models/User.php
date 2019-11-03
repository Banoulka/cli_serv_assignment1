<?php

require_once "Model.php";
require_once "Post.php";
require_once "Notification.php";

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

    public function addToWatchList($postID)
    {
        // Insert the watchlist row
        parent::setCustomClassAndTable("Post", "user_watchlist");
        parent::insert()->value("user_id", $this->id)->value("post_id", $postID)->execute();
        $postAdded = Post::find(["id" => $postID]);

        // Send a notification to the owner of the post
        $notif = new Notification();
        $notif->user_id_from = $this->id;
        $notif->user_id_to = $postAdded->user()->id;
        $notif->type = Notification::WATCH_TO_USER;
        $notif->link = "/posts/view.php?post_id=$postID";
        $notif->save();
    }

    public function isOnWatchList($postID)
    {
        $watchlist = $this->watchlist();
        foreach ($watchlist as $post) {
            if ($postID == $post->id)
                return true;
        }
        return false;
    }

    // Relationships

    /**
     * Get all posts by user
     *
     * @return Post[]
     * */
    public function posts(): array
    {
        return Post::findAllByKey(["user_id" => $this->id]);
    }

    /**
     * Get all notifications to user
     *
     * @return Notification[]
     * */
    public function notifications()
    {
        parent::setCustomClassAndTable("Notification", "user_notifications");
        // Get all notifications relating to the user
        return parent::findAllByKey(["user_id_to" => $this->id]);
    }

    /**
     * Get all posts on users watchlist
     *
     * @return Post[]
     * */
    public function watchlist()
    {
        $posts = array();
        parent::setCustomClassAndTable("", "user_watchlist");
        $watchListEntries = parent::findAllByKey(["user_id" => $this->id]);
        foreach ($watchListEntries as $entry) {
            array_push($posts, Post::find(["id" => $entry->post_id]));
        }
        return $posts;
    }
}