<?php
session_start();

require_once "Captcha.php";
require_once "Session.php";

$captcha = unserialize(Session::getSession("captcha"));

$image = $captcha->getImage();

header("Content-type: image/jpeg");
imagejpeg($image);
imagedestroy($image);