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
$view->title = "Create New Post - uGame";
$view->page = "create-post";
$view->tags = Tag::all();

if (isset($_POST["submit"])) {

    $post = new Post();
    $post->title = htmlentities($_POST["title"]);
    $post->description = htmlentities($_POST["desc"]);
    $post->body = htmlentities($_POST["body"]);
    $post->type_stage = strtolower(htmlentities($_POST["type_stage"]));
    $post->user_id = Authentication::User()->id;

    $tags = isset($_POST["tags"]) ? $_POST["tags"] : array();

    $validation = new Validation();
    $validation->name("title")->value($post->title)->required()->length(0, 19);
    $validation->name("description")->value($post->description)->required()->length(0, 220);
    $validation->name("body")->value($post->body)->required()->length(0, 1500);
    $validation->name("type_stage")->value($post->type_stage)->required();
    $validation->name("tags")->value(count($tags))->length(0, 4);

    if ($_FILES["cover_image"]["name"] != "") {
        $file = true;
        $imageFileType =  strtolower(pathinfo($_FILES["cover_image"]["name"], PATHINFO_EXTENSION ));
        $targetDir = "../uploads/post_covers/";
    }

    if (!$validation->isSuccess()) {
//        var_dump($validation->getErrors());
        $view->errors = $validation->getErrors();
        $view->post = $post;
    } else {
        $post->save();
        // do the post tags
        $post->addTags($tags);

        // Process files
        if ($file) {
            $postFileName = "post-$post->id.$imageFileType";
            $post->cover_image = "/uploads/post_covers/" .$postFileName;
            $targetFile = $targetDir . $postFileName;
            //TODO: file checks
            $post->save();
            move_uploaded_file($_FILES["cover_image"]["tmp_name"], $targetFile);
        }

        Route::redirect("view.php?post_id=$post->id");
    }
}

require_once "../views/posts/create.phtml";