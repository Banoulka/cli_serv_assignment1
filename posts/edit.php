<?php

session_start();
spl_autoload_register(
    function ($className) {
        include_once "../Models/lib/" . $className . ".php";
    }
);

require_once "../Models/Post.php";

// Require authentication to get to this page
require_once "../auth.php";

$view = new stdClass();
$view->title = "Edit Post - uGame";
$view->pageName = "edit-post";
$view->tags = Tag::all();

if (isset($_GET["post_id"])) {
    // Find the post associated with the id in the url
    $view->post = Post::find(["id" => $_GET["post_id"]]);

    // Check if the current logged in user is the owner of the post
    if ($view->post) $view->owner = Authentication::User()->id == $view->post->user()->id;

    if (isset($_POST["submit"]) && Authentication::User() == $view->owner) {
        $formData = [
            "title" => htmlentities($_POST["title"]),
            "description" => htmlentities($_POST["description"]),
            "body" => htmlentities($_POST["body"]),
            "type_stage" => htmlentities($_POST["type_stage"])
        ];

        $validation = new Validation();
        $validation->name("Title")->value($formData["title"])->required()->length(0, 20);
        $validation->name("Description")->value($formData["description"])->required()->length(0, 220);
        $validation->name("Body")->value($formData["body"])->required()->length(0, 1500);
        $validation->name("Type Stage")->value($formData["type_stage"])->required();

        if ($validation->isSuccess()) {
            // save the post etc
            $post = $view->post;
            $post->title = $formData["title"];
            $post->description = $formData["description"];
            $post->body = $formData["body"];
            $post->type_stage = $formData["type_stage"];
            $post->save();
            Route::redirect("view.php?post_id=$post->id");
        } else {
            // Send errors back with form data
            $view->formData = $formData;
            $view->formErrors = $validation->getErrors();
        }
    }

    require_once "../Views/posts/edit.phtml";
} else {
    Route::redirect("../games.php");
}



