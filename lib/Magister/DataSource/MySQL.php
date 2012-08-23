<?php

/**
 * MySQLDataSource class.
 * 
 * Provides data from a MySQL database.
 */
class MySQLDataSource extends DataSource {
    
    /**
     * Data source constructor.
     * 
     * Instanciates the PDO connection.
     * 
     * @param array $config
     */
    public function __construct(array $config) {
        parent::__construct("mysql:host={$config['host']};dbname={$config['name']};port={$config['port']}", $config['user'], $config['pass']);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}
