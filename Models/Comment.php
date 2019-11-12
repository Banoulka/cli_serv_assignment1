<?php

require_once "Model.php";
require_once "User.php";
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
     * @return Comment[]
     * */
    protected static function all()
    {
        return parent::getAllByTableName();
    }

    /**
     * Get one from table using keyValue
     *
     * @param $keyValueArr array
     *
     * @return mixed
     */
    protected static function find($keyValueArr)
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
        parent::deleteModel($this->id);
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
    }

    public function isOwner(User $user)
    {
        return $user->id == $this->user_id;
    }

    public function likesCount()
    {
        return 20;
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