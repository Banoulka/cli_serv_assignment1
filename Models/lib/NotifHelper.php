<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/Models/Notification.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Models/lib/Session.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Models/lib/Route.php";

if (isset($_POST["addNotifLimit"])) {
    session_start();
    if (!Session::isSet("notif_limit")) {
        Session::setSession("notif_limit", 30);
    } else {
        Session::setSession("notif_limit", Session::getSession("notif_limit") + 15);
    }
    Route::redirect($_SERVER["HTTP_REFERER"]);
}

if (isset($_GET["notif_id"])) {
    // Process the notification
    $notif = Notification::find(["id" => $_GET["notif_id"]]);
    if (!$notif->isRead())
        $notif->setRead();
}