<?php

/**
 * Main Magister exception. 
 * 
 * All Magister exceptions extend this one.
 * 
 * @package Magister
 * @subpackage Error 
 */
class MagisterException extends Exception {
    
}

/**
 * Routing exception. 
 * 
 * Thrown when no route matches the given URL, the controller 
 * does not exist or the action does not exist.
 * 
 * @package Magister
 * @subpackage Error 
 */
class RoutingException extends MagisterException {
    
}

/**
 * URL exception. 
 * 
 * Thrown when the system cannot find a route matching the name 
 * that was given for generation.
 * 
 * @package Magister
 * @subpackage Error 
 */
class UrlException extends MagisterException {
    
}

/**
 * Undefined method exception. 
 * 
 * Thrown when an app tried to call an undefined 
 * method.
 * 
 * @package Magister
 * @subpackage Error 
 */
class UndefinedMethodException extends MagisterException {
    
}

/**
 * Unknown DataSource exception.
 * 
 * Thrown when your app specifies a DataSource that is not known by your 
 * installation of Magister.
 * 
 * @package Magister
 * @subpackage Error
 */
class UnknownDataSourceException extends MagisterException {
    
}

/**
 * Unknown relation exception.
 * 
 * Thrown when your app tries to access a relation in an object that has not 
 * been defined.
 * 
 * @package Magister
 * @subpackage Error
 */
class UnknownRelationException extends MagisterException {
    
}

/**
 * I18n exception.
 * 
 * Thrown on any I18n error.
 * 
 * @package Magister
 * @subpackage Error
 */
class I18nException extends MagisterException {
    
}

/**
 * Duplicate key exception.
 * 
 * Thrown when the system detects that a key has already been defined.
 * 
 * @package Magister
 * @subpackage Error
 */
class DuplicateKeyException extends MagisterException {
    
}

class FileNotFoundException extends MagisterException {
    
}

class ViewException extends MagisterException {
    
}

class UnknownConfigurationException extends MagisterException {
    
}
