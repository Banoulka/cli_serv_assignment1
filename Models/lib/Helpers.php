<?php

const BASEURL = "http://localhost:8000";

class Helpers {

    /**
     * Method to echo active if the current page equals the title
     *
     * @param $view string The page title to check against
     * @param $page string The current page
     *
     * @return void
     */
    public static function echoActive($view, $page)
    {
        if ($view == $page ) {
            echo "active";
        } else {
            echo "";
        }
    }

    public static function isexternal($url) {
        $components = parse_url($url);
        return !empty($components['host']) && strcasecmp($components['host'], parse_url(BASEURL)["host"]); // empty host will indicate url like '/relative.php'
    }

    public static function printIfExternail($url) {
        return Helpers::isexternal(BASEURL . $url) ? $url : BASEURL . $url;
    }

    /**
     * Function to return the time since posted to a human readable
     * format
     *
     * @param $timestamp int
     *
     * @return string
     */
    public static function getTimeSince($timestamp)
    {
        $postDateTime = new DateTime();
        $postDateTime->setTimestamp($timestamp);
        $nowDateTime = new DateTime();

        $difference = $nowDateTime->diff($postDateTime);
        if ($difference->y > 0) {
            // Print full date plus year
            return $postDateTime->format("j F Y \a\\t G:i");
        }
        if ($difference->d > 1) {
            // Print full date
            return $postDateTime->format("j F \a\\t G:i");

        } else if ($difference->d == 1) {
            // Print yesterday and time
            return "Yesterday at " . $postDateTime->format("H:i");

        } else if ($difference->h > 0) {
            // Print hours
            return $difference->h . " hr" . ($difference->h != 1 ? "s" : "");

        } else if ($difference->i > 0) {
            // Print minutes
            return $difference->i . " min" . ($difference->i != 1 ? "s" : "");

        } else if ($difference->s > 30) {
            // Print seconds
            return $difference->s . " sec";

        } else {
            // Print just now
            return "just now";
        }
    }

    /**
     * Function to return the time since posted to a human readable
     * format
     *
     * @param $timestamp int
     *
     * @return string
     */
    public static function getTimeSinceMin($timestamp)
    {
        $postDateTime = new DateTime();
        $postDateTime->setTimestamp($timestamp);
        $nowDateTime = new DateTime();

        $difference = $nowDateTime->diff($postDateTime);
        if ($difference->y > 0) {
            // Print full date plus year
            return $postDateTime->format("j F Y \a\\t G:i");
        }
        if ($difference->d > 1) {
            // Print days
            return $difference->d . " d";

        } else if ($difference->h > 0) {
            // Print hours
            return $difference->h . " h";

        } else if ($difference->i > 0) {
            // Print minutes
            return $difference->i . " m";

        } else if ($difference->s > 30) {
            // Print seconds
            return $difference->s . " s";

        } else {
            // Print just now
            return "now";
        }
    }
}