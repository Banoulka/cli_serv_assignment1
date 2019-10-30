<?php

define("HOMEDIR", __DIR__ . "/../../");
require_once HOMEDIR . "/Models/User.php";

class Authentication {

    private static $sessionID = "user";
    public static $err;

    private static function logonUser(User $user) {
        Session::setSession(self::$sessionID, serialize($user));
    }

    public static function logout() {
        if (self::isLoggedOn()) {
            Session::removeSession(self::$sessionID);
        }
    }

    public static function validateAndLogonUser($email, $password) {
        self::$err = [];
        $foundUser = User::findByEmail($email);
        if ($foundUser) {
            // User exists, check user and password
            $verify = password_verify($password, $foundUser->password);
            if($verify)
                self::logonUser($foundUser);
            else
                array_push(self::$err, "Username and password does not match");
            return $verify;
        }
        array_push(self::$err, "User does not exist");
        return false;
    }

    public static function isLoggedOn() {
        return Session::isSet(self::$sessionID);
    }

    public static function User() {
        return unserialize(Session::getSession(self::$sessionID));
    }
}