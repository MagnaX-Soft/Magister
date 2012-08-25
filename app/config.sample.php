<?php
/**
 * Application configuration. Any configuration in config.php can be overwritten
 * by creating a config.local.php file.
 * 
 * @package App
 * @subpackage Config 
 */

/**
 * Database configuration.
 * 
 * The required settings depend on the datasource (type) used.
 */
$dbConfig = array(
    'type' => 'mysql',
    'host' => 'localhost',
    'name' => 'magister',
    'user' => 'root',
    'pass' => '',
    'port' => 3306,
    'prefix' => 'mag'
);

/**
 * If your website is in a directory, set it here. The path must start with a 
 * slash, but not end with one. 
 */
$routingConfig = array(
    'basePath' => '',
);

/**
 * This string is used to hash passwords.
 * 
 * The longer the better.
 */
$passwordHash = 'PUT A RANDOM STRING HERE';

/**
 * If using PHP < 5.3, set to true.
 */
$compatibilityMode = false;
if (version_compare(PHP_VERSION, '5.3.0', '<'))
    $compatibilityMode = true;

/**
 * Debug mode. 
 */
$debugMode = true;

/**
 * Session configuration.
 * 
 * Alphanumerics, undescores and dashes only.
 */
$sessionConfig = array(
    'name' => 'Magister_App'
);

/**
 * Sets the timezone.
 */
date_default_timezone_set('America/Toronto');

/**
 * Defines the configuration variables as global.
 */
global $dbConfig, $routingConfig, $passwordHash, $compatibilityMode, $debugMode, $sessionConfig;

Autoload::loadApp('config.local');
