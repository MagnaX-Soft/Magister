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
Config::set('DB.type', 'mysql');
Config::set('DB.host', 'localhost');
Config::set('DB.name', 'magister');
Config::set('DB.user', 'root');
Config::set('DB.pass', '');
Config::set('DB.port', 3306);
Config::set('DB.prefix', 'mag');

/**
 * If your website is in a directory, set it here. The path must start with a
 * slash, but not end with one.
 */
Config::set('routing.basePath', '');

/**
 * This string is used to hash passwords.
 *
 * The longer the better.
 */
Config::set('security.hash.password', 'PUT A RANDOM STRING HERE');

/**
 * Compatibility mode is used to emulate certain features in PHP < 5.3. It is
 * automatically set to the correct value at runtime.
 */
Config::set('mode.compatibility', false);
$compatibilityMode = false;
if (version_compare(PHP_VERSION, '5.3.0', '<'))
    Config::set('mode.compatibility', true);

/**
 * Debug mode.
 */
Config::set('mode.debug', false);

/**
 * Session configuration.
 *
 * Alphanumerics, undescores and dashes only.
 */
Config::set('session.name', 'Magister_App');

/**
 * Log configuration.
 *
 * The path can either be a directory (files for different levels are
 * automatically created) or the special value syslog. Not logging errors is
 * bad, so you should always log errors.
 */
Config::set('log.enabled', true);
Config::set('log.location', APP_DIR);

/**
 * Sets the timezone.
 */
date_default_timezone_set('America/Toronto');

// Loads local app configuration.
Autoload::loadApp('config.local');
