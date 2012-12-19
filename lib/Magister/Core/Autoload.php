<?php

/**
 * Autoload class.
 * 
 * Handles the loading of the various classes and files required for the 
 * framework to function. The system makes use of SPL's class autoloading 
 * functionnality to load only the required files and classes as they are needed 
 * by the application. The functions can be called manually to force the loading 
 * of a file or class, or to load a file that contains no class.
 * 
 * @package Magister
 * @subpackage Core
 */
class Autoload {

    /**
     * LoadApp method.
     * 
     * Searches for and loads a file in the application directory.
     * 
     * @param string $name Name of the file or class.
     * @param string $dir The subdirectory where the file is located. Can be `null`.
     * @param string $ext Extension with a leading dot. Defaults to `.php`.
     * @return bool Status of the load.
     */
    public static function loadApp($name, $dir = null, $ext = '.php') {
        $file = APP_DIR . DS . ((null !== $dir) ? $dir . DS : '') . $name . $ext;
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }

    /**
     * LoadLib method.
     * 
     * Searches for and loads a library file by name.
     * 
     * @param string $name Name of the file or class.
     * @param string $dir The subdirectory where the file is located. Can be `null`.
     * @param string $ext Extension with a leading dot. Defaults to `.php`.
     * @return bool Status of the load.
     */
    public static function loadLib($name, $dir = null, $ext = '.php') {
        $common = LIB_DIR . DS . ((null !== $dir) ? $dir . DS : '') . $name . $ext;
        if (file_exists($common)) {
            require_once $common;
            return true;
        }
        return false;
    }

    /**
     * LoadAppLib method.
     * 
     * Loads a custom lib file in the application directory.
     * 
     * @param string $name Name of the file or class.
     * @return bool Status of the load.
     */
    public static function loadAppLib($name) {
        return self::loadApp($name, 'Lib');
    }

    /**
     * LoadAppController method.
     * 
     * Searches for and loads a controller by name.
     * 
     * @param string $name Name of the controller.
     * @return bool Status of the load.
     */
    public static function loadAppController($name) {
        if (false !== strpos($name, 'Controller'))
            return self::loadApp($name, 'Controllers');
        return false;
    }

    /**
     * LoadAppModel method.
     * 
     * Searches for and loads a model by name.
     * 
     * @param string $name Name of the Model.
     * @return bool Status of the load.
     */
    public static function loadAppModel($name) {
        if (false !== strpos($name, 'Model'))
            return self::loadApp($name, 'Models');
        return false;
    }

    /**
     * LoadAppRow method.
     * 
     * Searches for and loads an row by name
     * 
     * @param string $name Name of the row.
     * @return bool Status of the load.
     */
    public static function loadAppRow($name) {
        return self::loadApp(Inflect::pluralize($name) . 'Model', 'Models');
    }

    /**
     * LoadLibCore method.
     * 
     * Searches for and loads a core file by name.
     * 
     * @param string $name Name of the file or class.
     * @return bool Status of the load.
     */
    public static function loadLibCore($name) {
        return self::loadLib($name, 'Core');
    }

    /**
     * LoadLibHelper method.
     * 
     * Searches for and loads a helper by name.
     * 
     * @param string $name Name of the Helper.
     * @return bool Status of the load.
     */
    public static function loadLibHelper($name) {
        if (false !== strpos($name, 'Helper'))
            return self::loadLib(ucfirst(strtolower(compat_strstr($name, 'Helper', true))), 'Helpers');
        return false;
    }

    /**
     * loadLibModel method.
     * 
     * Loads files in the Model directory in the Magister lib location.
     * 
     * @param string $name Name of the class.
     * @return bool Status of the load.
     */
    public static function loadLibModel($name) {
        return self::loadLib($name, 'Model');
    }

    /**
     * LoadLibDataSource method.
     * 
     * Searches for and loads a datasource by name.
     * 
     * @param string $name Name of the datasource.
     * @return bool Status of the load.
     */
    public static function loadLibDataSource($name) {
        if (false !== strpos($name, 'DataSource'))
            return self::loadLib(compat_strstr($name, 'DataSource', true), 'Model' . DS . 'DataSource');
        return false;
    }

    /**
     * LoadLibException method.
     * 
     * Loads all exceptions.
     * 
     * @param string $name Name of the exception.
     * @return bool Status of the load.
     */
    public static function loadLibException($name) {
        if (false !== strpos($name, 'Exception'))
            return self::loadLib('Exceptions');
        return false;
    }

}

/**
 * The order is not so important, as a missing class will just trigger a new 
 * loading sequence. However, it is best to put the functions that test the 
 * class name first, simply because they either return fast (it's a string 
 * check) or load the class and end the loading sequence.
 */
spl_autoload_register(array('Autoload', 'loadLibException'));
spl_autoload_register(array('Autoload', 'loadAppController'));
spl_autoload_register(array('Autoload', 'loadAppModel'));
spl_autoload_register(array('Autoload', 'loadLibHelper'));
spl_autoload_register(array('Autoload', 'loadLibDataSource'));
spl_autoload_register(array('Autoload', 'loadAppLib'));
spl_autoload_register(array('Autoload', 'loadApp'));
spl_autoload_register(array('Autoload', 'loadLibCore'));
spl_autoload_register(array('Autoload', 'loadLibModel'));
spl_autoload_register(array('Autoload', 'loadLib'));
spl_autoload_register(array('Autoload', 'loadAppRow'));
