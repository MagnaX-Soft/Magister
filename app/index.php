<?php

/**
 * Website entrypoint
 * @package App
 */
if (!defined('DS'))
/**
 * Shortname for the directory separator.
 */
    define('DS', DIRECTORY_SEPARATOR);


if (!defined('ROOT'))
/**
 * The path to the root of the package. 
 */
    define('ROOT', dirname(dirname(__FILE__)));

if (!defined('APP'))
/**
 * The name of the current app.
 */
    define('APP', basename(dirname(__FILE__)));

if (!defined('APP_DIR'))
/**
 * The path to the root of the current app.
 */
    define('APP_DIR', dirname(__FILE__));

if (!defined('LIB_DIR'))
/**
 * The path to the root of the library.
 * Change it only if you have moved the library to another location.
 */
    define('LIB_DIR', ROOT . DS . 'lib');

require_once LIB_DIR . DS . 'Magister' . DS . 'Core' . DS . 'Autoload.php';
Autoload::loadApp('config');
Autoload::loadLib('Functions');
Autoload::loadApp('routes');

run();
