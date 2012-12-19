<?php

/**
 * MySQLDataSource class.
 * 
 * Provides data from a MySQL database.
 * 
 * @package Magister
 * @subpackage DB
 */
class SQLiteDataSource extends DataSource {
    
    /**
     * Data source constructor.
     * 
     * Instanciates the PDO connection.
     */
    public function __construct() {
        parent::__construct("sqlite:" . Config::get('DB.host'), null, null, array(PDO::ATTR_PERSISTENT => true));
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}
