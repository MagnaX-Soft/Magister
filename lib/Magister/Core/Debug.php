<?php

class Debug {
    
    /**
     * Debug data to be displayed after the footer.
     * @var array 
     */
    private static $data;
    
    /**
     * Adds the value of var_dump to the data array.
     * @param mixed $arg1 
     * @param mixed ...
     * @param mixed $argn
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
     * Adds the query list.
     * @param Model $model 
     */
    public static function queries(Model $model) {
        self::dump($model->dumpQueries());
    }
    
    /**
     * Returns HTML formatted debug information.
     * @return string
     */
    public static function display() {
        return (!empty(self::$data)) ? '<div class="span-24 last debug">' . implode('', self::$data) . '</div>' : '';
    }
}

