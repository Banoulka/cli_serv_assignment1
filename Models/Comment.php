<?php

require_once "Model.php";
require_once "User.php";
require_once "Notification.php";
require_once "Tag.php";
require_once "Comparable.php";

/**
 * @property int id
 * @property int timestamp
 * @property int user_id
 * @property int post_id
 * @property string body
 */
class Comment extends Model implements Comparable
{

    public function __construct()
    {
        parent::__construct([
            "user_id",
            "post_id",
            "body",
            "timestamp",
        ]);
    }

    /**
     * Get all from table
     *
     * @return self[]
     * */
    public static function all()
    {
        self::setClassAndTable();
        return parent::getAllByTableName();
    }

    /**
     * Get one from table using keyValue
     *
     * @param $keyValueArr array
     *
     * @return self
     */
    public static function find($keyValueArr)
    {
        self::setClassAndTable();
        return parent::findOneByKey($keyValueArr);
    }

    /**
     * Set class and table to use by SQL queries.
     * Must always be called before doing data and class takes
     *
     * @return void
     */
    protected static function setClassAndTable()
    {
        parent::setCustomClassAndTable("Comment", "post_comments");
    }

    public function destroy()
    {
        self::setClassAndTable();
        parent::deleteModel(["id" => $this->id]);

        parent::delete()->value("user_id_from", $this->user_id)->value("type", Notification::COMMENT_TO_USER_POST)->value("link", "/posts/view.php?post_id=$this->post_id")->executeDelete();
    }

    /**
     * Save the model to the database
     *
     * @return void
     */
    public function save()
    {
        self::setClassAndTable();
        $now = new DateTime();
        $this->timestamp = $now->getTimestamp();
        parent::saveModel();

        // Send notification to user
        $notification = new Notification();
        $notification->user_id_to = Post::find(["id" => $this->post_id])->user()->id;
        $notification->user_id_from = $this->user_id;
        $notification->type = Notification::COMMENT_TO_USER_POST;
        $notification->link = "/posts/view.php?post_id=$this->post_id";
        // Only save is the commenter is not the same
        if ($notification->user_id_from != $notification->user_id_to) {
            $notification->save();
        }
    }

    public function isOwner(User $user)
    {
        return $user->id == $this->user_id;
    }

    public function likesCount()
    {
        parent::setCustomClassAndTable("", "comment_likes");
        return count(parent::findAllByKey(["comment_id" => $this->id]));
    }

    /**
     * Custom compare to function
     *
     * @param Comparable $self this object
     * @param Comparable $other other object
     *
     * @return int Int -1, 0 or 1 Depending on result of comparison
     * @throws Exception
     */
    public static function compareTo(Comparable $self, Comparable $other)
    {
        if ($other instanceof Comment) {
            return $self->timestamp < $other->timestamp ? 1 : -1;
        }
        return 0;
    }

    // Relationships ================================

    /**
     *
     * @return User
     * */
    public function user()
    {
        parent::setCustomClassAndTable("User", "users");
        return parent::findOneByKey(["id" => $this->user_id]);
    }
}