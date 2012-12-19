<?php

/**
 * MySQLDataSource class.
 *
 * Provides data from a MySQL database.
 *
 * @package Magister
 * @subpackage DB
 */
class MySQLDataSource extends DataSource {

    /**
     * Data source constructor.
     *
     * Instanciates the PDO connection.
     */
    public function __construct() {
        parent::__construct("mysql:host=" . Config::get('DB.host')  . ";dbname=" . Config::get('DB.name')  . ";port=" . Config::get('DB.port'), Config::get('DB.user'), Config::get('DB.pass'), array(PDO::ATTR_PERSISTENT => true));
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}
