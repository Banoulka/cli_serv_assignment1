<?php

require_once "Model.php";
require_once "Post.php";
require_once "Notification.php";

/**
 * @property string first_name
 * @property string last_name
 * @property string display_name
 * @property string display_pic
 * @property string email
 * @property string bio
 * @property string password
 * @property-read int id
 *
 * */
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
    /**
     * @return User[]
     * */
    public static function all()
    {
        self::setClassAndTable();
        return parent::getAllByTableName();
    }

    // Find user by id

    /**
     * @param array $keyValueArr
     * @return User
     */
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
            if (isset($this->display_pic)) {
                $this->display_pic = "/uploads/profile_pictures/" . $this->display_pic;
            }
            parent::saveModel();
            return true;
        }
    }

    public function addToWatchList($postID)
    {
        // Insert the watchlist row
        parent::setCustomClassAndTable("", "user_watchlist");
        parent::insert()->value("user_id", $this->id)->value("post_id", $postID)->executeInsert();
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

    public function isLiked($postID)
    {
        $likes = $this->likes();
        foreach ($likes as $post) {
            if ($postID == $post->id)
                return true;
        }
        return false;
    }

    public function isFollower(User $user)
    {
        $followers = $user->followers();
        $me = User::find(["id" => $this->id]);
        return in_array($me, $followers, FALSE);
    }

    public function likePost($postID)
    {
        parent::setCustomClassAndTable("", "post_likes");
        parent::insert()->value("user_id", $this->id)->value("post_id", $postID)->executeInsert();
        $postLiked = Post::find(["id" => $postID]);

        // notification
        $notif = new Notification();
        $notif->user_id_to = $postLiked->user()->id;
        $notif->user_id_from = $this->id;
        $notif->type = Notification::LIKE_TO_USER_POST;
        $notif->link = "/posts/view.php?post_id=$postLiked->id";
        $notif->save();
    }

    public function unLikePost($postID)
    {
        parent::setCustomClassAndTable("", "post_likes");
        parent::delete()->value("user_id", $this->id)->value("post_id", $postID)->executeDelete();

//        $post = Post::find(["id" => $postID]);
//        // Del notification
//        parent::setCustomClassAndTable("", "user_notifications");
//        parent::delete()->value("user_id_from", $this->id)->value("user_id_to", $post->user()->id)->value("type", Notification::LIKE_TO_USER_POST)->value("link", "/posts/view.php?post_id=$postId")->executeDelete();
    }

    public function unWatchPost($postID)
    {
        parent::setCustomClassAndTable("", "user_watchlist");
        parent::delete()->value("user_id", $this->id)->value("post_id", $postID)->executeDelete();
    }

    public function followUser(User $user)
    {
        parent::setCustomClassAndTable("User", "user_follows");
        parent::insert()->value("user_id_to", $user->id)->value("user_id_from", $this->id)->executeInsert();
        // Notification
        $notif = new Notification();
        $notif->user_id_to = $user->id;
        $notif->user_id_from = $this->id;
        $notif->type = Notification::FOLLOW_TO_USER;
        $notif->link = "/users/view.php?id=$notif->user_id_from";
        $notif->save();
    }

    public function unFollowUser(User $user)
    {
        parent::setCustomClassAndTable("User", "user_follows");
        parent::delete()->value("user_id_to", $user->id)->value("user_id_from", $this->id)->executeDelete();
    }

    public function messageUser(User $user, string $message)
    {
        $now = new DateTime();
        // Send a message to the user
        self::setCustomClassAndTable("", "user_messages");
        parent::insert()
            ->value("user_id_from", $this->id)
            ->value("user_id_to", $user->id)
            ->value("body", $message)
            ->value("timestamp", $now->getTimestamp())
            ->executeInsert();
        FlashMessager::addMessage("Successfully messaged " . $user->name(), "primary", ["> $message"]);
    }

    // Relationships

    /**
     * Get all posts by user
     *
     * @return Post[]
     * */
    public function posts()
    {
        parent::setCustomClassAndTable("Post", "posts");
        $posts = Post::findAllByKey(["user_id" => $this->id]);
        usort($posts, array("Post", "compareTo"));
        return $posts;
    }

    /**
     * @return Post[]
     */
    public function recentPosts()
    {
        parent::setCustomClassAndTable("Post", "posts");
        $sql = "SELECT * FROM posts 
                WHERE user_id = $this->id
                ORDER BY time DESC
                LIMIT 6";
        return parent::query($sql);
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
        usort($posts, array("Post", "compareTo"));
        return $posts;
    }

    /**
     * Get all posts on users watchlist
     *
     * @return Post[]
     * */
    public function likes()
    {
        $posts = array();
        parent::setCustomClassAndTable("", "post_likes");
        $likesEntry = parent::findAllByKey(["user_id" => $this->id]);
        foreach ($likesEntry as $entry)
        {
            array_push($posts, Post::find(["id" => $entry->post_id]));
        }
        return $posts;
    }

   /**
    * Get all posts on users watchlist
    *
    * @return User[]
    * */
    public function followers()
    {
        $followers = array();
        parent::setCustomClassAndTable("", "user_follows");
        $followerIDs = parent::findAllByKey(["user_id_to" => $this->id]);
        foreach ($followerIDs as $entry)
        {
            array_push($followers, User::find(["id" => $entry->user_id_from]));
        }
        return $followers;
    }

    /**
     * @return User[]
     */
    public function usersWithMessages()
    {
        parent::setCustomClassAndTable("", "user_messages");
        $messages = parent::findAllByKey(["user_id_to" => $this->id]);
        $usersWithMessages = array();
        foreach ($messages as $message) {

            $userFrom = User::find(["id" => $message->user_id_from]);
            unset($message->user_from->password);

            if (array_key_exists($userFrom->email, $usersWithMessages)) {
                $user = $usersWithMessages[$userFrom->email];
                if (array_key_exists("messages", $user)) {
                    array_push($user->messages, $message);
                } else {
                    $user->messsages = array($message);
                }
            } else {
                $userFrom->messages = array($message);
                $usersWithMessages[$userFrom->email] = $userFrom;
            }
        }
        return $usersWithMessages;
    }

}