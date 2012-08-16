<?php

class MySQLDataSource extends DataSource {
    public function __construct(array $config) {
        parent::__construct("mysql:host={$config['host']};dbname={$config['name']};port={$config['port']}", $config['user'], $config['pass']);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}
