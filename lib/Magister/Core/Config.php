<?php

/**
 * Config class.
 *
 * Handles the configuration for the current app.
 *
 * @package Magister
 * @subpackage Core
 */
class Config {

    /**
     * The configuration data.
     *
     * @var array
     */
    private static $data = array();

    /**
     * Set function.
     *
     * Sets a configuration key to a value.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        self::$data[$key] = $value;
    }

    /**
     * Get function.
     *
     * If $strict is true, throws an UnknownConfigurationException when the key
     * has not been defined. If $strict is false, returns $default.
     *
     * @param string $key
     * @param boolean $strict defaults to true
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $strict = true, $default = '') {
        if (!array_key_exists($key, self::$data) && $strict == true)
            throw new UnknownConfigurationException($key);
        elseif (!array_key_exists($key, self::$data) && $strict != true)
            return $default;

        return self::$data[$key];
    }

    public static function exists($key) {
        return array_key_exists($key, self::$data);
    }

    /**
     * NotEmpty function.
     *
     * Returns true if the $key is defined and is not an empty value.
     *
     * @param string $key
     * @return bool
     */
    public static function notEmpty($key) {
        return self::exists($key) && !empty(self::$data[$key]);
    }
}
