<?php


class Messages extends Controller
{
    public function typing($id) {
        $options = array(
            "cluster" => "eu",
            "useTLS" => true,
        );

        $pusher = new \Pusher\Pusher(
            '8f49b51adc7ccf8e85a4',
            'd7bca9bca512968c4550',
            '940465',
            $options,
        );

        $data = new stdClass();
        $data->typing = $_POST["typing"] == "false" ? false : true;
        $fromUser = $_POST["from"];

        $pusher->trigger("msg-to-$id-from-$fromUser", 'other-type', $data);
        $this->send(200);
    }

    public function conversation($id) {
        $conversation = new Coversation($id);
        $data = new stdClass();
        $data->messages = $conversation->getMessages();
        $this->send(200, $data);
    }
}