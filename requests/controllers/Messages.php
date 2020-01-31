<?php


class Messages extends Controller
{
    public function typing($id) {
        $data = new stdClass();
        $data->typing = $_POST["typing"] == "false" ? false : true;
        $fromUser = $_POST["from"];

        Pusher::getInstance()->trigger("msg-to-$id-from-$fromUser", 'other-type', $data);
        $this->send(200);
    }

    public function conversation($id) {
        $conversation = new Coversation($id);
        $data = new stdClass();
        $data->messages = $conversation->getMessages();
        $data->userFrom = $conversation->userFrom;
        $this->send(200, $data);
    }

    public function new() {
        $userFrom = $_POST["from"];
        $userTo = $_POST["to"];
        $messageBody = htmlentities($_POST["message"]);
        $read = $_POST["read"] == "true" ? true : false;

        $sql = "INSERT INTO user_messages (user_id_to, user_id_from, body, `read`, timestamp)
                VALUES (:uto, :ufrom, :body, :isRead, :timestamp)";
        $stmt = Database::getInstance()->getdbConnection()->prepare($sql);
        $stmt->bindParam(":ufrom", $userFrom);
        $stmt->bindParam(":uto", $userTo);
        $stmt->bindParam(":body", $messageBody);
        $stmt->bindParam(":isRead", $read);
        $date = new DateTime();
        $timestamp = $date->getTimestamp();
        $stmt->bindParam(":timestamp", $timestamp);

        $data = new stdClass();
        if ($stmt->execute()) {

            // Message insert successful, get the last message and return it
            $id = Database::getInstance()->getdbConnection()->lastInsertId();
            $sql = "SELECT * FROM user_messages WHERE id = $id";
            $stmt = Database::getInstance()->getdbConnection()->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_OBJ);
            $stmt->execute();

            $sent = $stmt->fetch();
            $sent->timestamp = Helpers::getTimeSinceMin($sent->timestamp);

            $data->message = $sent;
            $data->userFrom = User::find(["id" => $userFrom]);
            $data->userFrom->display_pic = Helpers::printIfExternal($data->userFrom->display_pic);

            $data->userTo = User::find(["id" => $userTo]);
            $data->userTo->display_pic = Helpers::printIfExternal($data->userTo->display_pic);

            Pusher::getInstance()->trigger("msg-to-$userTo-from-$userFrom", "new-msg", $data);
            $this->send(201, $data);

        } else {
            $data->error = "An error occured";
            $this->send(400, $data);
        }
    }
}