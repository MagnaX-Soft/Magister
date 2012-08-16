<?php

/**
 * Main Magister exception.
 * @package Magister
 * @subpackage Error 
 */
class MagisterException extends Exception {
    
}

/**
 * Routing exception. Thrown when no route matches the given URL, the controller 
 * does not exist or the action does not exist.
 * @package Magister
 * @subpackage Error 
 */
class RoutingException extends MagisterException {
    
}

/**
 * URL exception. Thrown when the system cannot find a route matching the name 
 * that was given for generation.
 * @package Magister
 * @subpackage Error 
 */
class UrlException extends MagisterException {
    
}

/**
 * UndefinedMethodException. Thrown when an app tried to call an undefined 
 * method.
 * @package Magister
 * @subpackage Error 
 */
class UndefinedMethodException extends MagisterException {
    
}

class UnknownDataSourceException extends MagisterException {
    
}
