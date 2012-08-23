<?php

/**
 * Entrypoint if there is no mod_rewrite and the document root cannot be set to 
 * the application directory.
 * @package Magister
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
    define('ROOT', dirname(__FILE__));

if (!defined('APP'))
/**
 * The name of the current app. Defaults to `app`.
 */
    define('APP', 'app');

if (!defined('APP_DIR'))
/**
 * The path to the root of the current app.
 */
    define('APP_DIR', ROOT . DS . APP);

if (!defined('WEB_DIR'))
/**
 * The path to the root of the web accessible directory.
 */
    define('WEB_DIR', APP_DIR . DS . 'Web');

require_once WEB_DIR . DS . 'index.php';
