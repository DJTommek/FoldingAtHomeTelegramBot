<?php

/**
 * Allow getting logs only to specific team(s)
 */
class Logs {

    public static function write(string $text) {
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $prefix = "[$date $time]";
        if (defined('LOG_ID')) {
            $prefix .= '[' . LOG_ID . ']';
        }
        file_put_contents(LOG_FOLDER . $date . '.log', $prefix . ' ' . $text . "\n", FILE_APPEND);
    }
}
