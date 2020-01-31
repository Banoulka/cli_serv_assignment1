<?php


abstract class Controller
{
    public function send($status, $dataObj = null) {
        if (!$dataObj) {
            $dataObj = new stdClass();
            $dataObj->message = "No data sent";
        }
        header("Content-Type: application/json");
        http_response_code($status);
        echo json_encode($dataObj);
    }
}