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
 * @property int balance
 *
 * */
class User extends Model implements JsonSerializable {

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
            "balance"
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

    /**
     * @param $rows
     * @return User[]|User
     */
    public static function random($rows)
    {
        self::setClassAndTable();
        return parent::random($rows);
    }

    public static function suggestUsers($suggestStr)
    {
        $sql = "SELECT *, MATCH(first_name) AGAINST ('+$suggestStr*' IN BOOLEAN MODE ) as relevance
                FROM users
                HAVING relevance > 0
                ORDER BY MATCH(first_name) AGAINST (':$suggestStr' IN NATURAL LANGUAGE MODE ) DESC
                LIMIT 15
                OFFSET 0;";
        $stmt = Database::getInstance()->getdbConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, "User");
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
        return QueryBuilder::getInstance()
            ->table('user_watchlist')
            ->where('post_id', $postID)
            ->where('user_id', $this->id)
            ->first();
    }

    public function isLiked($postID)
    {
        return QueryBuilder::getInstance()
            ->table('post_likes')
            ->where('post_id', $postID)
            ->where('user_id', $this->id)
            ->first();
    }

    public function isFollower(User $user)
    {
        $followers = $user->followers();
        $me = User::find(["id" => $this->id]);
        return in_array($me, $followers, FALSE);
    }

    public function likePost($postID)
    {
        if (!self::isLiked($postID)) {
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
        $data = new stdClass();
        $data->userFrom = $user->id;
        Pusher::getInstance()->trigger("msg-to-$user->id", "new-msg", $data);
    }

    public function pictureMessageUser(User $user, $imageFile)
    {
        $id = md5(microtime());
        $imageFileType =  strtolower(pathinfo($imageFile["name"], PATHINFO_EXTENSION ));
        $targetDir = "../uploads/picture_messages/";
        $userFileName = "msg-$id.$imageFileType";
        $targetFile = $targetDir . $userFileName;

        $now = new DateTime();
        $timestamp = $now->getTimestamp();

        $sql = "INSERT INTO picture_messages (user_id_from, user_id_to, picture_location, timestamp) 
                VALUES ($this->id, $user->id, \"$targetFile\", $timestamp)";

        $stmt = self::db()->prepare($sql);
        try {
            $stmt->execute();
            move_uploaded_file($imageFile["tmp_name"], $targetFile);
            return [];
        } catch(Exception $e) {
            return $e;
        }
    }

    public function destroy()
    {
        // Delete posts
        foreach (self::posts() as $post) {
            $post->destroy();
        }

        // Delete followers and following
        parent::setCustomClassAndTable("", "user_follows");
        parent::delete()->value("user_id_from", $this->id)->executeDelete();
        parent::delete()->value("user_id_to", $this->id)->executeDelete();

        // Delete comments
        parent::setCustomClassAndTable("", "post_comments");
        parent::delete()->value("user_id", $this->id)->executeDelete();

        // Delete likes
        parent::setCustomClassAndTable("", "post_likes");
        parent::delete()->value("user_id", $this->id)->executeDelete();

        // Delete user notifications
        parent::setCustomClassAndTable("", "user_notifications");
        parent::delete()->value("user_id_from", $this->id)->executeDelete();
        parent::delete()->value("user_id_to", $this->id)->executeDelete();

        // Delete watchlist
        parent::setCustomClassAndTable("", "user_watchlist");
        parent::delete()->value("user_id", $this->id)->executeDelete();

        // Delete the file if it exists
        if (!Helpers::isexternal($this->display_pic) && $this->display_pic != "/uploads/profile_pictures/nodp.png") {
            unlink(realpath("../" . $this->display_pic));
        }

        // Delete messages
        parent::setCustomClassAndTable("", "user_messages");
        parent::delete()->value("user_id_from", $this->id)->executeDelete();
        parent::delete()->value("user_id_to", $this->id)->executeDelete();

        // Delete the user
        self::setClassAndTable();
        parent::deleteModel(["id" => $this->id]);
    }

    public function buyGame($id): bool
    {
        $game = Post::find(["id" => $id]);
        $owner = $game->user();
        if ($game->price > $this->balance) {
            // Cannot buy
            return false;
        }
        $owner->balance += $game->price;
        $owner->save();

        $this->balance -= $game->price;
        $this->save();

        $notification = new Notification();
        $notification->type = Notification::BUY_TO_USER_GAME;
        $notification->user_id_from = Authentication::User()->id;
        $notification->user_id_to = $owner->id;
        $notification->link = "/posts/view.php?post_id=$id";
        $notification->save();
        return true;
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
     * @param $offset
     * @return Notification[]
     */
    public function notifications($offset)
    {
        // TODO: add preloading
        return Notification::allWithDataByID($this->id, $offset);
    }

    public function recentNotifications()
    {
        return Notification::allWithDataByID(
            $this->id,
            Session::isSet("notif_limit") ? Session::getSession("notif_limit") : 15);
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
     * @return object[]
     */
    public function messageSlimfo()
    {
        // Get all messages with user id and user info,
        // Ordering by time
        $sql = "SELECT id as user_id, first_name , last_name,  display_name, Messages.Messages as Messages, Messages.Unread as Unread, MAX(Messages.latest) as latest
                FROM (SELECT user_id_to, user_id_from, first_name, last_name, display_name, u.id, COUNT(u.id) as Messages, COUNT(case when `read` = 0 then 1 end) as Unread, MAX(timestamp) as latest
                      FROM user_messages
                               LEFT JOIN users u on user_messages.user_id_from = u.id
                      WHERE user_id_to = $this->id
                      GROUP BY u.id, user_id_from UNION SELECT user_id_to, user_id_from, first_name, last_name, display_name, u.id, 0 as Messages, 0 as Unread, MAX(timestamp) as latest
                      FROM user_messages
                               LEFT JOIN users u on user_messages.user_id_to = u.id
                      WHERE user_id_from = $this->id
                      GROUP BY user_id_to) as Messages
                GROUP BY id
                ORDER BY latest DESC;";
        $results = self::db()->query($sql, PDO::FETCH_OBJ)->fetchAll();
        foreach ($results as $result) {
            $result->latest = Helpers::getTimeSince($result->latest);

            $uFrom = $result->user_id;
            $uTo = $this->id;

            $sql = "SELECT body FROM user_messages
                    WHERE (user_id_to = $uFrom
                    AND user_id_from = $uTo)
                    OR (user_id_from = $uFrom AND user_id_to = $uTo)
                    ORDER BY timestamp DESC
                    LIMIT 1";
            $result->lmessage = self::db()->query($sql)->fetch(PDO::FETCH_COLUMN);
        }
        return $results;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $this->display_pic = Helpers::printIfExternal($this->display_pic);
        $this->name = $this->name();
        $this->followerCount = count($this->followers());
        return $this;
    }
}