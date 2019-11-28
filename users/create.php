<?php

require_once "../Models/User.php";

session_start();
spl_autoload_register(function ($className) {
    require_once __DIR__ . "/../Models/lib/" . $className . ".php";
});

if (Authentication::isLoggedOn() ) {
    Route::redirect("../games.php");
}

$view = new stdClass();
$view->title = "Sign Up - uGame";
$view->pageName = "signup";

if (isset($_POST["submit"])) {

    $view->formData = [
        "first_name" => htmlentities($_POST["first_name"]),
        "last_name" => htmlentities($_POST["last_name"]),
        "email" => htmlentities($_POST["email"]),
        "password" => htmlentities($_POST["password"]),
        "confirm_password" => htmlentities($_POST["confirm_password"]),
    ];
    $validation = new Validation();
    $validation->name("Email")->value($view->formData["email"])->required()->type(FILTER_VALIDATE_EMAIL);
    $validation->name("Password")->value($view->formData["password"])->required()->length(3, 50);
    $validation->name("First Name")->value($view->formData["first_name"])->required()->length(0, 30);
    $validation->name("Last Name")->value($view->formData["last_name"])->required()->length(0, 30);
    $validation->name("Password Confirmation")->value($view->formData["confirm_password"])->required()->length(0, 30)->equal($view->formData["password"]);

    if (!$validation->isSuccess()) {
        // Send errors back to the signup page
        $view->formErrors = $validation->getErrors();
    } else {
        $user = User::findByEmail($view->formData["email"]);
        if($user) {
            $view->errors = ["This user already exists"];
        } else {
            // if user does not exist
            $user = new User();
            $user->first_name = $view->formData["first_name"];
            $user->last_name = $view->formData["last_name"];
            $user->email = $view->formData["email"];
            $user->password = $view->formData["password"];
            $user->save();

            // Login user
            Authentication::validateAndLogonUser($user->email, $_POST["password"]);
            Authentication::refresh();
            Route::redirect("/games.php");
        }
    }

}

$page = new stdClass();
$page->signup = true;
require_once("../Views/users/register-login.phtml");