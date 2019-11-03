<?php


if (!Authentication::isLoggedOn()) {
    Route::redirect("/users/create.php");
}