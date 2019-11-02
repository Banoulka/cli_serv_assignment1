<?php

require_once "Model.php";
require_once "User.php";
require_once "Tag.php";
require_once "Comparable.php";

class Notification extends Model
{

    // Type of notifcations
    const LIKE_TO_USER_POST = "like-to-user-post";
    const FOLLOW_TO_USER = "follow-to-user";
    const WATCH_TO_USER = "watch-to-user";
    const LIKE_TO_USER_COMMENT = "like-to-user-comment";
    const REPLY_TO_USER_COMMENT = "reply-to-user-comment";
    const UPLOAD_BY_FOLLOWED = "upload-by-followed";
    const WATCHLIST_UPDATE = "watchlist-update";
    const COMMENT_TO_USER_POST = "comment-to-user-post";

    // Set the class and table of the model
    protected static function setClassAndTable()
    {
        parent::$className = "Notification";
        parent::$tableName = "user_notifications";
    }

    // Writable attributes to be pulled from the database
    public function __construct()
    {
        // Construct with attributes
        parent::__construct(
            [
            "user_id_from",
            "user_id_to",
            "type",
            "read",
            ]
        );
    }

    public static function find($keyValueArr)
    {
        // TODO: Implement find() method.
    }

    public function save()
    {
        self::setClassAndTable();
        $now = new DateTime();
        $this->time = $now->getTimestamp();
        parent::saveModel();
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    /**
     * Get all notifications (shouldnt be used)
     *
     * @return void
     */
    public static function all()
    {
        // TODO: Implement all() method.
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

    public function getMessage()
    {
        switch ($this->type)
        {
        case self::FOLLOW_TO_USER:
            return "started following you.";
        case self::LIKE_TO_USER_POST:
            return "liked your post.";
        case self::LIKE_TO_USER_COMMENT:
            return "liked your comment.";
        case self::WATCH_TO_USER:
            return "added your post to their watchlist.";
        case self::UPLOAD_BY_FOLLOWED:
            return "uploaded a new post.";
        case self::WATCHLIST_UPDATE:
            return "A post on your watchlist has had an update.";
        case self::REPLY_TO_USER_COMMENT:
            return "replied to your comment.";
        case self::COMMENT_TO_USER_POST:
            return "commented on your post.";
        default:
            return "nill type";
        }
    }

    public function getIconClass()
    {
        switch ($this->type)
        {
        case self::FOLLOW_TO_USER:
            return "fas fa-user-friends";
        case self::LIKE_TO_USER_POST:
        case self::LIKE_TO_USER_COMMENT:
            return "fas fa-thumbs-up";
        case self::WATCH_TO_USER:
            return "fas fa-eye";
        case self::UPLOAD_BY_FOLLOWED:
        case self::WATCHLIST_UPDATE:
            return "fas fa-rss";
        case self::REPLY_TO_USER_COMMENT:
        case self::COMMENT_TO_USER_POST:
            return "fas fa-comment-dots";
        default:
            return "";
        }
    }

    // Relationships ============================

    /**
     * Method to get the user who the notification
     * is to
     *
     * @return User
     * */
    public function userTo(): User
    {
        parent::setCustomClassAndTable("User", "users");
        return parent::find(["id" => $this->user_id_to]);
    }

    /**
     * Method to get the user who the notification
     * is from
     *
     * @return User
     * */
    public function userFrom(): User
    {
        parent::setCustomClassAndTable("User", "users");
        return parent::findOneByKey(["id" => $this->user_id_from]);
    }
}