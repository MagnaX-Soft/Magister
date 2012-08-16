<?php

/**
 * Helps with URL generation.
 * @package Magister
 * @subpackage Helpers 
 */
class UrlHelper {

    /**
     * Returns the url prefixed by the basePath.
     * @param string $url
     * @return string 
     */
    public static function link($url) {
        return Router::getInstance()->prefixURL($url);
    }

    /**
     * Returns the URL to an asset.
     * @uses urlHelper()
     * @param string $type
     * @param string $name
     * @return string 
     */
    public static function asset($type, $name) {
        return self::link('/assets/' . $type . '/' . $name);
    }

    /**
     * Generates and returns the URL for a named route.
     * @param string $route
     * @param array $params
     * @return string 
     */
    public static function route($route, array $params = array()) {
        return Router::getInstance()->generate($route, $params);
    }

    /**
     * Builds a query string from the supplied array
     * @param array $query
     * @return string 
     */
    public static function query(array $query) {
        $singleQuery = array();
        foreach ($query as $name => $key) {
            if (is_array($key)) {
                $name .= '[]';
                foreach ($key as $value) 
                    $singleQuery[] = $name . '=' . $value;
            } else {
                $singleQuery[] = $name . '=' . $key;
            }
        }
        return '?' . implode('&', $singleQuery);
    }

}
