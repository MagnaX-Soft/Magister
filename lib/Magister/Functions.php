<?php
/**
 * Various miscellaneous functions.
 * @package Magister 
 * @subpackage Core
 */
set_error_handler('errorToExceptionHandler');
register_shutdown_function('fatalErrorShutdownHandler');

/**
 * Transforms regular PHP errors into exceptions.
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
 * Processes shutdowns to display the custom error messages if the shutdown was 
 * triggered by a fatal error. 
 */
function fatalErrorShutdownHandler() {
    $last_error = error_get_last();
    if ($last_error['type'] === E_ERROR) {
        // fatal error
        ob_clean();
        displayError($last_error['message'] . ' in ' . $last_error['file'] . ' on line ' . $last_error['line'] . '.');
    }
}

/**
 * Hashes input string using md5 and sha1 algorithms.
 * @global string $passwordHash
 * @param string $string The clear-text password.
 * @return string The hashed password.
 */
function createHash($string) {
    global $passwordHash;
    return md5(sha1(sha1(md5(sha1(md5(sha1(md5(sha1(sha1(sha1(md5(sha1(md5(md5(md5(sha1(md5(sha1(md5(sha1(sha1(sha1(sha1(md5(sha1(md5(md5(sha1(md5($string . $passwordHash))))))))))))))))))))))))))))));
}

/**
 * Redirects the client to the $location.
 * @param string $location The redirect location.
 */
function redirect($location) {
    header('Location: ' . $location);
    exit();
}

/**
 * Returns true if all the keys specified in $array are set and not empty in the
 * $param array.
 * @param array $param The array to check.
 * @param array $array An array of required fields.
 * @return bool 
 */
function set_filled(array $param, array $array) {
    foreach ($array as $field) {
        if (!isset($param[$field]) || empty($param[$field]))
            return false;
    }
    return true;
}

/**
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
        displayError('The requested URL was not found. Please <a href="javascript:history.go(-1)">go back</a>.<pre>' . $e->getMessage() . '</pre>', 'Page Not Found', '404 Not Found');
    } catch (Exception $e) {
        ob_clean();
        displayError($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . '.');
    }
    ob_end_flush();
}

/**
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
    global $compatibilityMode;
    if ($compatibilityMode) {
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
 * Returns the value of the specified key or default is the key is not defined.
 * @param array $array The array to search.
 * @param string|int $key The key to retrieve.
 * @param mixed $default The default value of the key.
 * @return mixed The default value if the key is not set, the value of the key 
 * otherwise.
 */
function getValue(array $array, $key, $default = '') {
    return (isset($array[$key])) ? $array[$key] : $default;
}

/**
 * Basic error functionality. 
 * @param string $message error message
 * @param string $title title of the error page
 * @param string $http HTTP error string
 */
function displayError($message, $title = 'Server Error', $http = '500 Internal Server Error') {
    header('HTTP/1.1 ' . $http);
    header('Content-type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <title>Error</title>
            <style type="text/css"><?php echo file_get_contents(APP_DIR . DS . 'assets' . DS . 'css' . DS . 'screen.css'); ?></style>
        </head>
        <body>
            <div class="container">
                <div class="span-24 last append-bottom prepend-top" id="header">
                    <div class="span-24 last" id="title">
                        <h1><?php echo $title; ?></h1>
                    </div>
                    <div class="span-24 last">
                        <div class="error">
                            <strong>
                                <p><?php echo $message; ?></p>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
    <?php
    exit(1);
}