<?php

class Page extends Controller
{
    public function index() {
        $data = new stdClass();
        $data->message = "hello index";
        $this->send(200, $data);
    }
}