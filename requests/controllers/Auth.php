<?php


class Auth extends Controller
{
    public function me() {
        $data = new stdClass();
        if (Authentication::isLoggedOn()) {
            $data->user = Authentication::User();
            $data->user->name = $data->user->name();
            $this->send(200, $data);
        } else {
            $data->error = "User not logged in";
            $this->send(401, $data);
        }
    }

    public function login() {
        if (!Authentication::validateAndLogonUser($_POST["email"], $_POST["password"])) {
            $data = new stdClass();
            $data->error = "User and pass do not match";
            $this->send(400, $data);
        } else {
            $this->send(200, Authentication::User());
        }
    }
}