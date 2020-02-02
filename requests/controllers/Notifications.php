<?php


class Notifications extends Controller
{
    public function index($offset = 0)
    {
        $data = new stdClass();

        if (Authentication::isLoggedOn()) {

            $notifications = Authentication::User()->notifications($offset * 10);
            $data->notifs = $notifications;
            $this->send(200, $data);

        } else {
            $data->error = "User not logged in";
            $this->send(401, $data);
        }
    }
}