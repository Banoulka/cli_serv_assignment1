<?php

if(isset($_POST )) {
    if(isset($_POST["minified"])) {
        echo "minified on";
    } else {
        echo "minifed off";
    }
}

//if(isset($_GET)) {
//    var_dump($_GET);
//
//    $searchType = $_GET["tags"];
//    foreach ($searchType as $type) {
//        echo $type . "<br/>";
//    }
//}

