<?php


class Pusher
{
    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            //checks if the pusher
            $options = array(
                "cluster" => "eu",
                "useTLS" => true,
            );

            // creates new instance if not, sending in connection info
            try {
                self::$instance = new \Pusher\Pusher(
                    '8f49b51adc7ccf8e85a4',
                    'd7bca9bca512968c4550',
                    '940465',
                    $options,
                );
            } catch (\Pusher\PusherException $e) {
                echo $e->getMessage();
            }
        }
        return self::$instance;
    }

}