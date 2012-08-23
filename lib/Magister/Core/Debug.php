<?php

/**
 * Debug class.
 * 
 * Generates dumps of functions.
 * 
 * @package Magister
 * @subpackage Debug
 */
class Debug {
    
    /**
     * Array holding the debug data.
     * 
     * @var array 
     */
    private static $data;
    
    /**
     * Dump method.
     * 
     * Adds the value of var_dump to the data array. Accepts an arbitrary number 
     * of arguments.
     * 
     * @param mixed $argn
     * @throws InvalidArgumentException
     */
    public static function dump() {
        if (func_num_args() < 1)
            throw new InvalidArgumentException(__CLASS__ . '::' . __METHOD__ . ' expects at least 1 argument.');
        foreach (func_get_args() as $arg) {
            ob_start();
            var_dump($arg);
            self::$data[] = ob_get_contents();
            ob_end_clean();
        }
    }
    
    /**
     * Queries method.
     * 
     * Adds the query list to the debug dump.
     * 
     * @param Model $model 
     */
    public static function queries(Model $model) {
        self::dump($model->dumpQueries());
    }
    
    /**
     * Display method.
     * 
     * Returns HTML formatted debug information.
     * 
     * @return string
     */
    public static function display() {
        return (!empty(self::$data)) ? '<div class="span-24 last debug">' . implode('', self::$data) . '</div>' : '';
    }
}

