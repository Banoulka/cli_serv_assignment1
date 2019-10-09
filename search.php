<?php

$view = new stdClass();
$view->page = "search";
$view->title = "Search - uGame";
$view->user = true;
$view->tags = ["Action", "Shooter", "Fighting", "Stealth", "Survival", "Adventure", "Horror"];

require_once("Views/search.phtml");