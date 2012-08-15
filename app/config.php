<?php

/**
 * Application configuration. Any configuration in config.php can be
 * @package App
 * @subpackage Config 
 */
date_default_timezone_set('America/Toronto');

global $DBConfig, $routingConfig, $passwordHash, $compatibilityMode, $sessionConfig;

/**
 * DB configuration 
 */
$DBConfig = array(
    'host' => 'localhost',
    'name' => '',
    'user' => '',
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
 */
$passwordHash = 'PUT A RANDOM STRING HERE';

/**
 * If using PHP < 5.3, set to true.
 */
$compatibilityMode = false;
if (version_compare(PHP_VERSION, '5.3.0', '<'))
    $compatibilityMode = true;

/**
 * Session configuration.
 */
$sessionConfig = array(
    'name' => 'Magister App'
);

Autoload::loadApp('config.local');
