<?php

/**
 * DB class.
 * 
 * Factory for data sources.
 * 
 * @package Magister
 * @subpackage DB
 */
class DB {

    /**
     * getDataSource method.
     * 
     * Returns a datasource.
     *
     * @return DataSource
     * @throws UnknownDataSourceException 
     */
    public static function getDataSource() {
        switch (Config::get('DB.type')) {
            case 'mysql':
                return new MySQLDataSource();
            case 'sqlite':
            case 'sqlite3':
                return new SQLiteDataSource();
            default:
                throw new UnknownDataSourceException('The ' . Config::get('DB.type') . ' datasource is not registered in this application');
        }
    }

}
