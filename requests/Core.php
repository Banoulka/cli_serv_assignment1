<?php

class Core {

    protected $currentController = "Page";
    protected $currentMethod = "index";
    protected $params = array();

    public function __construct() {

        $url = $this->getUrl();
        // [0] - [posts]
        // [1] - [method]

        // Look in controllers for first value
        if(file_exists("./controllers/" . ucwords($url[0]) . ".php")) {
            $this->currentController = ucwords($url[0]);
            // Unset 0 Index
            unset($url[0]);
        }

        // Require the controller
        require_once("./controllers/" . $this->currentController . ".php");

        // Instantiate controller
        $this->currentController = new $this->currentController;

        // Check for second parameter of URL - method
        if(isset($url[1])) {
            // Check to see if method exists in controller
            if(method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // Get parameters
        $this->params = $url ? array_values($url) : [];

        // Call a callback with array of paramters
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl() {
        $params = isset($_SERVER["PATH_INFO"]) ? explode("/", $_SERVER["PATH_INFO"]) : null;
        if (!$params) {
            $params[0] = "/";
        }
        foreach ($params as $index => $param) {
            if ($param == "") {
                unset($params[$index]);
            }
        }
        $params = array_values($params);
        return $params;
    }
}