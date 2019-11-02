<?php


if (!Authentication::isLoggedOn()) {
    Route::redirect("create.php");
}