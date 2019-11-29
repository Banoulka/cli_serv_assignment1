<?php

require_once "Captcha.php";
require_once "Session.php";

$captcha = unserialize(Session::getSession("captcha"));
$string = $captcha->getPhrase();

header("Content-type: image/png");