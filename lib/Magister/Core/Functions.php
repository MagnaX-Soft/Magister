<?php

/**
 * Various miscellaneous functions.
 * @package Magister
 * @subpackage Core
 */
/**
 * Error handlers.
 */
error_reporting(E_ALL | E_STRICT);
set_error_handler('errorToExceptionHandler');
register_shutdown_function('fatalErrorShutdownHandler');

/**
 * ErrorToExceptionHandler function.
 *
 * Transforms regular PHP errors into exceptions.
 *
 * @param int $errno The error number.
 * @param string $errstr The error message.
 * @param string $errfile The offending file.
 * @param int $errline The offending line.
 * @throws ErrorException
 */
function errorToExceptionHandler($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}

/**
 * FatalErrorShutdownHandler function.
 *
 * Processes shutdowns to display the custom error messages if the shutdown was
 * triggered by a fatal error.
 */
function fatalErrorShutdownHandler() {
    $last_error = error_get_last();
    if ($last_error['type'] === E_ERROR) {
        // fatal error
        $errorException = new ErrorException($last_error['message'], 1, 0, $last_error['file'], $last_error['line']);
        View::getInstance()->renderError($errorException);
    }
}

/**
 * CreateHash function.
 *
 * Hashes input string using md5 and sha1 algorithms.
 *
 * @param string $string The clear-text password.
 * @return string The hashed password.
 */
function createHash($string) {
    $string .= Config::get('security.hash.password');
    return md5(sha1(sha1(md5(sha1(md5(sha1(md5(sha1(sha1(sha1(md5(sha1(md5(md5(md5(sha1(md5(sha1(md5(sha1(sha1(sha1(sha1(md5(sha1(md5(md5(sha1(md5($string))))))))))))))))))))))))))))));
}

/**
 * SetFilled function.
 *
 * Returns true if all the keys specified in $array are set and not empty in the
 * $param array. Throws an InvalidArgumentException if a required key is not set
 * in $param.
 *
 * @param array $param The array to check.
 * @param array $array An array of required fields.
 * @return bool
 * @throws InvalidArgumentException
 */
function setFilled(array $param, array $array) {
    foreach ($array as $field) {
        if (!isset($param[$field]) || empty($param[$field]))
            throw new InvalidArgumentException(sprintf(__('Required field \'%s\' was not present.', 'magister'), $field));
    }
    return true;
}

/**
 * Run functions.
 *
 * Processes the current request and dispatches the correct action.
 */
function run() {
    try {
        Session::start();
        I18n::determineLanguage();

        Router::getInstance()->setBasePath(Config::get('routing.basePath'));
        $route = Router::getInstance()->matchCurrentRequest();

        $target = $route->getTarget();
        $target['model'] = $target['controller'] . 'Model';
        $target['controller'] .= 'Controller';

        $reflex = new ReflectionClass($target['controller']);
        if (!$reflex->hasMethod($target['action']))
            throw new RoutingException(sprintf(__("Controller '%s' has no '%s' action.", 'magister'), $target['controller'], $target['action']));

        $instance = $reflex->newInstance($route->getParameters(), $target);
        $instance->{$target['action']}();
    } catch (RoutingException $e) {
        View::getInstance()->renderError($e, null, '404');
    } catch (Exception $e) {
        View::getInstance()->renderError($e);
    }
}

/**
 * Compat_strstr function.
 *
 * Implements PHP 5.3's version of strstr for compability with earlier versions.
 *
 * @param string $haystack The input string.
 * @param string $needle If needle is not a string, it is converted to an
 * int and applied as the ordinal value of a character.
 * @param bool $before_needle If TRUE, strstr() returns the part of the haystack
 * before the first occurrence of the needle (excluding the needle).
 * @return bool|string The portion of string, or FALSE if needle is not
 * found.
 */
function compat_strstr($haystack, $needle, $before_needle = false) {
    if (Config::get('mode.compatibility')) {
        if ($before_needle) {
            if (strpos($haystack, $needle) === false)
                return false;
            return substr($haystack, 0, strpos($haystack, $needle));
        }
        return strstr($haystack, $needle);
    } else
        return strstr($haystack, $needle, $before_needle);
}

/**
 * GetValue function.
 *
 * Returns the value of the specified key or default is the key is not defined.
 *
 * @param array $array The array to search.
 * @param string|int $key The key to retrieve.
 * @param mixed $default The default value of the key.
 * @return mixed The default value if the key is not set, the value of the key
 * otherwise.
 */
function getValue(array $array, $key, $default = '') {
    return (isset($array[$key])) ? stripslashes($array[$key]) : $default;
}

/**
 * Translates the given string in the given domain.
 *
 * @param string $string
 * @param string $domain
 * @return string
 */
function __($string, $domain = APP) {
    return I18n::translate($string, $domain);
}

/**
 * Translates and prints the given string in the given domain.
 *
 * @param string $string
 * @param string $domain
 */
function __e($string, $domain = APP) {
    echo I18n::translate($string, $domain);
}

/**
 * Encodes the input string for displaying in html.
 *
 * @param string $text
 * @return string
 */
function h($text) {
    return htmlspecialchars($text, ENT_QUOTES);
}

/**
 * Prints the html encoded text.
 *
 * @see h()
 * @param type $text
 */
function eh($text) {
    echo h($text);
}


/**
 * Returns true if all the keys in the array are integers.
 *
 * @param array $array
 * @return boolean
 */
function is_indexed(array &$array) {
    foreach (array_keys($array) as $key) {
        if (!is_int($key))
            return false;
    }
    return true;
}
