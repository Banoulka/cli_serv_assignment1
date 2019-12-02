<?php


class WebScrapDefense
{

    private $classesAndStyles;


    public function __construct()
    {
        $this->classesAndStyles = [];
    }

    public function echoClass($className)
    {
        $this->classesAndStyles[$className] = [
            "class_name" => $className,
            "styles" => $this->getStyles($className),
            "gen_name" => $this->genClassName()
        ];
        return $this->classesAndStyles[$className]["gen_name"];
    }

    public function echoStyles($className)
    {
        $classArr = $this->classesAndStyles[$className];
        return "." . $classArr["gen_name"] . $classArr["styles"];
    }

    public function getStyles($className)
    {
        // Read the styles from the styles file,
        // remove the class names regexpression?
        $css = file_get_contents("css/sassStyles.css");

        echo "helllo";
        die();
        return "{color: red;}";
    }

    private function genClassName()
    {
        $charSet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        return substr(str_shuffle(str_repeat($charSet, 5)), 0, 10);
    }
}