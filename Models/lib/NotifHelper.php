<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/Models/Notification.php";

if (isset($_GET["notif_id"])) {
    // Process the notification
    $notif = Notification::find(["id" => $_GET["notif_id"]]);
    if (!$notif->isRead())
        $notif->setRead();
}