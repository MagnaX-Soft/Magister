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
     * @param string $ext Extension with a leading dot. Defaults to `.php`.
     * @return bool Status of the load.
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
     * LoadAppLib method.
     * 
     * Loads a custom lib file in the application directory.
     * 
     * @param string $name Name of the file or class.
     * @return bool Status of the load.
     */
    public static function loadAppLib($name) {
        return self::loadApp('Lib' . DS . $name);
    }

    /**
     * LoadController method.
     * 
     * Searches for and loads a controller by name.
     * 
     * @param string $name Name of the controller.
     * @return bool Status of the load.
     */
    public static function loadController($name) {
        if (strpos($name, 'Controller') !== false) {
            return self::loadApp('Controllers' . DS . $name);
        }
        return false;
    }

    /**
     * LoadModel method.
     * 
     * Searches for and loads a model by name.
     * 
     * @param string $name Name of the Model.
     * @return bool Status of the load.
     */
    public static function loadModel($name) {
        if (strpos($name, 'Model') !== false) {
            return self::loadApp('Models' . DS . $name);
        }
        return false;
    }

    /**
     * LoadObject method.
     * 
     * Searches for and loads an object by name
     * 
     * @param string $name Name of the object.
     * @return bool Status of the load.
     */
    public static function loadObject($name) {
        if (!class_exists('RowObject'))
            self::loadLib('Model');
        return self::loadApp('Models' . DS . Inflect::pluralize($name) . 'Model');
    }

    /**
     * LoadLib method.
     * 
     * Searches for and loads a library file by name.
     * 
     * @param string $name Name of the file or class.
     * @param string $ext Extension with a leading dot. Defaults to `.php`.
     * @return bool Status of the load.
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
     * LoadCore method.
     * 
     * Searches for and loads a core file by name.
     * 
     * @param string $name Name of the file or class.
     * @return bool Status of the load.
     */
    public static function loadCore($name) {
        return self::loadLib('Core' . DS . $name);
    }

    /**
     * LoadHelper method.
     * 
     * Searches for and loads a helper by name.
     * 
     * @param string $name Name of the Helper.
     * @return bool Status of the load.
     */
    public static function loadHelper($name) {
        if (strpos($name, 'Helper') !== false) {
            self::loadLib('Helpers' . DS . ucfirst(compat_strstr($name, 'Helper', true)));
        }
        return false;
    }

    /**
     * LoadDataSource method.
     * 
     * Searches for and loads a datasource by name.
     * 
     * @param string $name Name of the datasource.
     * @return bool Status of the load.
     */
    public static function loadDataSource($name) {
        if (strpos($name, 'DataSource') !== false) {
            self::loadLib('DataSource' . DS . compat_strstr($name, 'DataSource', true));
        }
        return false;
    }

    /**
     * LoadException method.
     * 
     * Loads all exceptions.
     * 
     * @param string $name Name of the exception.
     * @return bool Status of the load.
     */
    public static function loadException($name) {
        if (strpos($name, 'Exception') !== false) {
            return self::loadLib('Exceptions');
        }
        return false;
    }

}

/* The order can always be improved, but I currently set it so that it first 
 * goes through the loaders that check for the name, no matter where their 
 * location, then go through the application-specific loaders, than the library 
 * loaders.
 */
spl_autoload_register(array('Autoload', 'loadException'));
spl_autoload_register(array('Autoload', 'loadController'));
spl_autoload_register(array('Autoload', 'loadModel'));
spl_autoload_register(array('Autoload', 'loadHelper'));
spl_autoload_register(array('Autoload', 'loadDataSource'));
spl_autoload_register(array('Autoload', 'loadObject'));
spl_autoload_register(array('Autoload', 'loadAppLib'));
spl_autoload_register(array('Autoload', 'loadApp'));
spl_autoload_register(array('Autoload', 'loadCore'));
spl_autoload_register(array('Autoload', 'loadLib'));
