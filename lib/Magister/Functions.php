<?php
/**
 * Various miscellaneous functions.
 * @package Magister
 * @subpackage Core
 */

/**
 * Error handlers.
 */
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
        ob_clean();
        Display::error($last_error['message'] . ' in ' . $last_error['file'] . ' on line ' . $last_error['line'] . '.');
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
 * Redirect function.
 *
 * Redirects the client to the specified location.
 *
 * @param string $location The redirect location.
 */
function redirect($location) {
    header('Location: ' . $location);
    exit();
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
            throw new InvalidArgumentException('Required field ' . $field . ' was not defined in given array');
    }
    return true;
}

/**
 * Run functions.
 *
 * Processes the current request and dispatches the correct action.
 */
function run() {
    ob_start();

    Session::start();

    try {
        $route = Router::getInstance()->matchCurrentRequest();

        $target = $route->getTarget();
        $target['model'] = $target['controller'] . 'Model';
        $target['controller'] .= 'Controller';

        $reflex = new ReflectionClass($target['controller']);
        if (!$reflex->hasMethod($target['action']))
            throw new RoutingException('Controller ' . $target['controller'] . ' has no action ' . $target['action'] . '.');
        $instance = $reflex->newInstance($route->getParameters());
        $instance->loadModel();

        $instance->{$target['action']}();
    } catch (RoutingException $e) {
        ob_clean();
        Display::error('The requested URL was not found. Please <a href="javascript:history.go(-1)">go back</a>.<pre>' . $e->getMessage() . '</pre>', 'Page Not Found', '404 Not Found');
    } catch (Exception $e) {
        ob_clean();
        Display::error($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . '.');
    }
    ob_end_flush();
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
    return (isset($array[$key])) ? $array[$key] : $default;
}
