<?php

/**
 * This file contains the Autoload functionality, and registers the loaders with 
 * SPL's hooks.
 * @package Magister 
 * @subpackage Core
 */
spl_autoload_register(array('Autoload', 'loadException'));
spl_autoload_register(array('Autoload', 'loadController'));
spl_autoload_register(array('Autoload', 'loadModel'));
spl_autoload_register(array('Autoload', 'loadHelper'));
spl_autoload_register(array('Autoload', 'loadDataSource'));
spl_autoload_register(array('Autoload', 'loadCore'));
spl_autoload_register(array('Autoload', 'loadLib'));
spl_autoload_register(array('Autoload', 'loadObject'));
spl_autoload_register(array('Autoload', 'loadAppLib'));
spl_autoload_register(array('Autoload', 'loadApp'));

/**
 * Handles the loading of the various classes and files required for the 
 * framework to function. The system makes use of SPL's class autoloading 
 * functionnality to load only the required files and classes as they are needed 
 * by the application. The functions can be called manually to force the loading 
 * of a file or class, or to load a file that contains no class.
 * @package Magister
 * @subpackage Core
 */
class Autoload {

    /**
     * Searches for and loads a file in the application directory, returning 
     * true if found and loaded, false otherwise.
     * @param string $name Name of the file or class.
     * @param string $ext Extension with a leading dot. Defaults to `.php`.
     * @return bool 
     */
    public static function loadApp($name, $ext = '.php') {
        $file = APP_DIR . DS . $name . $ext;
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }

    /**
     * Loads a custom lib file in the app directory
     * @param string $name Name of the file or class.
     * @return bool 
     */
    public static function loadAppLib($name) {
        return self::loadApp('lib' . DS . $name);
    }

    /**
     * Searches for and loads a controller by name, returning true if found and 
     * loaded, false otherwise.
     * @param string $name Name of the controller.
     * @return bool 
     */
    public static function loadController($name) {
        if (strpos($name, 'Controller') !== false) {
            return self::loadApp('Controllers' . DS . $name);
        }
        return false;
    }

    /**
     * Searches for and loads a model by name, returning true if found and 
     * loaded, false otherwise.
     * @param string $name Name of the Model.
     * @return bool 
     */
    public static function loadModel($name) {
        if (strpos($name, 'Model') !== false) {
            return self::loadApp('Models' . DS . $name);
        }
        return false;
    }

    /**
     * Searches for and loads an object by name, returning true if found and 
     * loaded, false otherwise.
     * @param string $name Name of the object.
     * @return bool 
     */
    public static function loadObject($name) {
        if (!class_exists('RowObject'))
            self::loadLib('Model');
        return self::loadApp('Models' . DS . Inflect::pluralize($name) . 'Model');
    }

    /**
     * Searches for and loads a library file by name, returning true if found 
     * and loaded, false otherwise.
     * @param string $name Name of the file or class.
     * @param string $ext Extension with a leading dot. Defaults to `.php`.
     * @return bool 
     */
    public static function loadLib($name, $ext = '.php') {
        $common = LIB_DIR . DS . 'Magister' . DS . $name . $ext;
        if (file_exists($common)) {
            require_once $common;
            return true;
        }
        return false;
    }

    /**
     * Searches for and loads a core file by name, returning true if found and 
     * loaded, false otherwise.
     * @param string $name Name of the file or class.
     * @return bool 
     */
    public static function loadCore($name) {
        return self::loadLib('Core' . DS . $name);
    }

    /**
     * Searches for and loads a helper by name, returning true if found and 
     * loaded, false otherwise.
     * @param string $name Name of the Helper.
     * @return bool 
     */
    public static function loadHelper($name) {
        if (strpos($name, 'Helper') !== false) {
            self::loadLib('Helpers' . DS . ucfirst(compat_strstr($name, 'Helper', true)));
        }
        return false;
    }

    /**
     * Searches for and loads a datasource by name, returning true if found and 
     * loaded, false otherwise.
     * @param string $name Name of the datasource.
     * @return bool 
     */
    public static function loadDataSource($name) {
        if (strpos($name, 'DataSource') !== false) {
            self::loadLib('DataSource' . DS . compat_strstr($name, 'DataSource', true));
        }
        return false;
    }

    /**
     * Loads all exceptions.
     * @param string $name Name of the exception.
     * @return bool 
     */
    public static function loadException($name) {
        if (strpos($name, 'Exception') !== false) {
            return self::loadLib('Exceptions');
        }
        return false;
    }

}
