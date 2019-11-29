<?php
require_once "Captcha.php";
require_once "Session.php";

session_start();

$captcha = unserialize(Session::getSession("captcha"));

$image = $captcha->getImage();

header("Content-type: image/jpeg");
imagejpeg($image);
imagedestroy($image);