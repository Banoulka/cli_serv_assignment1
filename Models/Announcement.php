<?php

require_once "Model.php";
require_once "User.php";
require_once "Notification.php";
require_once "Tag.php";
require_once "Comparable.php";

/**
 *
 * @property int id
 * @property int post_id
 * @property string body
 * @property int timestamp
 * */
class Announcement extends Model implements Comparable
{

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
        if ($self instanceof Announcement) {
            return $self->timestamp < $other->timestamp ? 1 : -1;
        }
        return 0;
    }

    /**
     * Get all from table
     *
     * @return self[]
     * */
    public static function all()
    {
        self::getAllByTableName();
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
        parent::setCustomClassAndTable("Announcement", "post_announcements");
    }

    /**
     * Save the model to the database
     *
     * @return void
     */
    public function save()
    {
        $now = new DateTime();
        $this->timestamp = $now->getTimestamp();
        self::setClassAndTable();
        parent::saveModel();
    }
}