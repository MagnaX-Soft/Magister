<?php

/**
 * Session class.
 * 
 * Handles all session-related matters. 
 * 
 * @package Magister
 * @subpackage Core
 */
class Session {

	/**
	 * Login method.
	 * 
	 * Logs the current user in.
	 */
	public static function login() {
		$_SESSION['login'] = true;
	}

	/**
	 * Logout method.
	 * 
	 * Logs the current user out.
	 */
	public static function logout() {
		$_SESSION['login'] = false;
	}

	/**
	 * IsLogin method.
	 * 
	 * Returns login status.
	 * 
	 * @return bool 
	 */
	public static function isLogin() {
		if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
			return true;
		}
		return false;
	}

	/**
	 * Start method.
	 * 
	 * Starts the session management. 
	 */
	public static function start() {
		session_name(Config::get('session.name'));
		session_start();
	}

	/**
	 * Destroy method.
	 * 
	 * Destroys current session (logs user out), and start a clean new one. 
	 */
	public static function destroy() {
		session_destroy();
		self::start();
		self::addMessage('success', 'You have been successfully logged out.');
	}

	/**
	 * AddMessage method.
	 * 
	 * Adds a flash message to the session.
	 * 
	 * @param string $type Either `notice`, `error`, `success` or `info`.
	 * @param string $content The message to display.
	 */
	public static function addMessage($type, $content) {
		if (!isset($_SESSION['messages']) || !is_array($_SESSION['messages']))
			$_SESSION['messages'] = array();

		$_SESSION['messages'][] = array('type' => $type, 'content' => $content);
	}

	/**
	 * GetMessages method.
	 * 
	 * Retrieves flash messages in HTML form. If $type is not specified, 
	 * retrieves all messages.
	 * 
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
