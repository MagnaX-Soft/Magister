<?php

/**
 * Handles all session-related matters. 
 * @package Magister
 * @subpackage Core
 */
class Session {

    /**
     * Logs the given user in.
     */
    public static function login() {
        $_SESSION['login'] = true;
    }

    /**
     * Returns login status.
     * @return bool 
     */
    public static function isUser() {
        if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
            return true;
        }
        return false;
    }

    /**
     * Starts the session management. 
     */
    public static function start() {
        global $sessionConfig;
        session_name($sessionConfig['name']);
        session_start();
        if (!isset($_SESSION['user'])) {
            $_SESSION['login'] = false;
        }
    }

    /**
     * Destroys current session (logs user out), and start a clean new one. 
     */
    public static function destroy() {
        session_destroy();
        self::start();
        self::addMessage('success', 'You have been successfully logged out.');
    }

    /**
     * Adds a flash message to the session.
     * @param string $type Either `notice`, `error`, `success` or `info`.
     * @param string $content The message to display.
     */
    public static function addMessage($type, $content) {
        if (!isset($_SESSION['messages']) || !is_array($_SESSION['messages']))
            $_SESSION['messages'] = array();

        $_SESSION['messages'][] = array('type' => $type, 'content' => $content);
    }

    /**
     * Retrieves flash messages in HTML form. If $type is not specified, 
     * retrieves all messages.
     * @param string $type Either `notice`, `error`, `success`, `info` or `all`.
     * Defaults to `all`.
     * @param bool $format Format the messages to html.
     * @return bool|string|array
     */
    public static function getMessages($type = 'all', $format = true) {
        if (!isset($_SESSION['messages']) || !is_array($_SESSION['messages']))
            return false;

        $theMessages = array();
        $return = '';

        foreach ($_SESSION['messages'] as $key => $message) {
            if ($type != 'all' && $message['type'] != $type)
                continue;
            $theMessages[$key] = $message;
        }
        foreach ($theMessages as $key => $message) {
            unset($_SESSION['messages'][$key]);
            if ($format) {
                $return .= '<div class="span-12 prepend-6 append-6 last">';
                $return .= "<div class=\"{$message['type']}\">{$message['content']}</div>";
                $return .= '</div>';
            }
        }
        return (empty($theMessages)) ? false : (($format) ? $return : $theMessages);
    }

}
