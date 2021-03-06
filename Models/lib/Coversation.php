<?php

class Coversation
{
    public $userMe;
    public $userFrom;

    public function __construct($userIdFrom)
    {
        $this->userMe = Authentication::User();
        $this->userFrom = User::find(["id" => $userIdFrom]);
    }

    public function getMessages()
    {
        $userFromID = $this->userFrom->id;
        $userToID = $this->userMe->id;

        $sql = "SELECT user_messages.body, user_messages.timestamp, user_messages.`read` ,IF(user_id_from = $userToID, true, false) as own
                FROM user_messages
                WHERE (user_id_from = $userFromID AND user_id_to = $userToID)
                OR (user_id_to = $userFromID AND user_id_from = $userToID)
                ORDER BY timestamp;";
        return Database::getInstance()->getdbConnection()->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    public static function markReadMessages($userIDTo)
    {
        $userMe = Authentication::User();
        $sql = "UPDATE user_messages SET `read` = 1
                WHERE user_id_from = $userIDTo AND user_id_to = $userMe->id";
        Database::getInstance()->getdbConnection()->exec($sql);
    }

    public static function unreadMessages($userIDTo)
    {
        $sql = "SELECT COUNT(case when user_messages.`read` = 0 then 1 end) as Unread
                FROM user_messages
                WHERE user_id_to = $userIDTo;";
        $number = Database::getInstance()->getdbConnection()->query($sql)->fetch(PDO::FETCH_COLUMN);
        return $number > 0 ? $number : null;
    }
}