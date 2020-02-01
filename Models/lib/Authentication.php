<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/Models/User.php";

class Authentication {

    private static $sessionID = "user";
    public static $err;

    public static function logonUser(User $user)
    {
        Session::setSession(self::$sessionID, serialize($user));
    }

    public static function logout()
    {
        if (self::isLoggedOn()) {
            if (isset($_COOKIE["a-login"]))
                setcookie("a-login", "", time() - 1, "/");
            session_destroy();
        }
    }

    public static function validateAndLogonUser($email, $password): bool
    {
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

    public static function isLoggedOn(): bool
    {
        return Session::isSet(self::$sessionID);
    }

    public static function User(): User
    {
        return unserialize(Session::getSession(self::$sessionID));
    }

    public static function refresh()
    {
        $refreshedUser = User::findByEmail(self::User()->email);
        Session::removeSession(self::$sessionID);
        Session::setSession(self::$sessionID, serialize($refreshedUser));
    }
}