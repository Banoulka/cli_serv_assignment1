<?php


class Users extends Controller
{
    public function suggest($string, $offset = 0) {
        $suggestedUsers = User::suggestUsers($string);

        $data = new stdClass();
        $data->suggestedUsers = $suggestedUsers;
        $this->send(200, $data);
    }
}