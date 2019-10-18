<?php

if(isset($_POST )) {
    var_dump($_POST);
}

if(isset($_GET)) {
    var_dump($_GET);

    $searchType = $_GET["tags"];
    foreach ($searchType as $type) {
        echo $type . "<br/>";
    }
}
