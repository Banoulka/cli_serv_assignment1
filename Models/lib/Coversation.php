<?php

class Coversation
{
    public $userMe;
    public $userFrom;

    public function __construct($userIdFrom)
    {
        $this->userMe = Authentication::User();
        $this->userFrom = User::find(["id" => $userIdFrom]);
        $this->userFrom->display_pic = Helpers::printIfExternal($this->userFrom->display_pic);
    }

    public function getMessages()
    {
        $userFromID = $this->userFrom->id;
        $userToID = $this->userMe->id;

        // Get the text messages

        $sql = "SELECT user_messages.body, user_messages.timestamp, user_messages.`read`, IF(user_id_from = $userToID, true, false) as own
                FROM user_messages
                WHERE (user_id_from = $userFromID AND user_id_to = $userToID)
                OR (user_id_to = $userFromID AND user_id_from = $userToID)
                ORDER BY timestamp;";
        $messages = Database::getInstance()->getdbConnection()->query($sql)->fetchAll(PDO::FETCH_OBJ);

        // Get the picture messages
        $sql2 = "SELECT picture_messages.picture_location, picture_messages.timestamp, IF(user_id_from = $userToID, true, false) as own
                FROM picture_messages
                WHERE (user_id_from = $userFromID AND user_id_to = $userToID)
                OR (user_id_to = $userFromID AND user_id_from = $userToID)
                ORDER BY timestamp";
        $pictures = Database::getInstance()->getdbConnection()->query($sql2)->fetchAll(PDO::FETCH_OBJ);

        array_map(function($picture) {
            $picture->picture = true;
            $picture->own = $picture->own == "1" ? true : false;
        }, $pictures);

        array_map(function($msg){
            $msg->own = $msg->own == "1" ? true : false;
            $msg->read = $msg->read == "1" ? true : false;
            $msg->picture = false;
        }, $messages);

        $totals = array_merge($messages, $pictures);
        usort($totals, function($a, $b) {
           return $a->timestamp <=> $b->timestamp;
        });

        array_map(function($item) {
            $item->timestamp = Helpers::getTimeSinceMin($item->timestamp);
        }, $totals);

        return $totals;
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