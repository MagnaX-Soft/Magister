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
 * Undefined Method Exception. 
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
 * Unknown DataSource Exception. Thrown when your app specifies a datasource 
 * that is not known by your install of Magister.
 * @package Magister
 * @subpackage Error
 */
class UnknownDataSourceException extends MagisterException {
    
}
