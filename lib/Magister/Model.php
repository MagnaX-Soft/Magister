<?php

/**
 * Base Model. Includes generic functionality 
 * @package Magister
 * @subpackage Model
 */
abstract class Model {

    /**
     * The PDO object
     * @var PDO 
     */
    protected $pdo;

    /**
     * The table related to the current Model.
     * @var string 
     */
    protected $table;

    /**
     * The class associated with single rows of the current Model
     * @var string 
     */
    protected $class;

    /**
     * Connects to the database
     */
    public function __construct() {
        $this->pdo = DB::getInstance()->pdo;
    }

    /**
     * Array of serializable properties.
     * @access private
     * @return array
     */
    public function __sleep() {
        return array('table', 'class');
    }

    /**
     * Re-connects to the database upon unserialization. 
     * @access private
     */
    public function __wakeup() {
        $this->__construct();
    }

    /**
     * Prefixes tables.
     * @global string $dbConfig
     * @param string $table
     * @return string 
     */
    private function getTableName($table) {
        global $dbConfig;

        return (!empty($dbConfig['prefix'])) ? $dbConfig['prefix'] . '_' . $table : $table;
    }

    /**
     * Gets the class associated with single rows of current Model.
     * @return string 
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * Gets a single record from the model by it's ID. If many records match the 
     * ID, only the first is returned.
     * @param int $id
     * @return RowObject|bool
     */
    public function getByID($id) {
        if ($this->doGetCount($this->table, array($id), 'id = ?') == 0)
            return false;

        $release = $this->doGet($this->table, array($id), 'id = ?');
        return $release->fetchObject($this->class);
    }

    /**
     * Gets all the columns from $table, accepts $params for prepared 
     * statements, $cond for conditions, $order for ordering and $limit for 
     * limits.
     * @param string $table
     * @param array $params
     * @param array|string $cond
     * @param array $order
     * @param string $limit
     * @param array|string $select
     * @return PDOStatement 
     */
    protected function doGet($table, array $params = null, $cond = null, array $order = null, $limit = null, $select = null) {
        $sql = 'SELECT ';
        $selectString = '*';
        if (!empty($select)) {
            if (is_string($select)) {
                $selectString = $select;
            } elseif (is_array($select)) {
                $selectString = implode(', ', $select);
            } else {
                return false;
            }
        }
        $sql .= $selectString . ' FROM ' . $this->getTableName($table);
        if (!empty($cond)) {
            $sql .= ' WHERE ';
            if (is_string($cond)) {
                $sql .= $cond;
            } elseif (is_array($cond)) {
                $sql .= implode(' AND ', $cond);
            } else {
                return false;
            }
        }
        if (!empty($order)) {
            $sql .= ' ORDER BY ';
            $strings = array();
            foreach ($order as $field => $dir)
                $strings[] = $field . ' ' . strtoupper($dir);
            $sql .= implode(', ', $strings);
        }
        if (!is_null($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }

        $query = $this->pdo->prepare($sql);
        if (is_null($params))
            $query->execute();
        else
            $query->execute($params);
        return $query;
    }

    /**
     * Counts the rows from $table, accepts $params and $cond.
     * @uses Model::get()
     * @param string $table
     * @param array $params
     * @param array|string $cond
     * @return int
     */
    protected function doGetCount($table, array $params = null, $cond = null) {
        $query = $this->doGet($table, $params, $cond, null, null, 'COUNT(*)');
        return (int) $query->fetchColumn();
    }

    /**
     * Inserts a row into the given table.
     * @param string $table
     * @param array $params
     * @param array $fields
     * @return bool|PDOStatement 
     */
    protected function doPut($table, array $params, array $fields) {
        if (count($fields) != count($params)) {
            return false;
        }
        $values = array();
        for ($i = 0; $i < count($fields); $i++) {
            $values[] = '?';
        }
        $sql = 'INSERT INTO ' . $this->getTableName($table) . '(' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';

        $query = $this->pdo->prepare($sql);
        if ($query->execute($params) === true)
            return true;
        else
            return $query;
    }

    /**
     * Updates an existing row in the database.
     * @param string $table
     * @param array $params
     * @param array $fields
     * @param array|string $cond 
     * @return bool|PDOStatement 
     */
    protected function doUp($table, array $params, array $fields, $cond) {
        if (count($fields) > count($params)) {
            return false;
        }
        $conds = array();
        if (is_string($cond)) {
            $conds[] = $cond;
        } elseif (is_array($cond)) {
            $conds = $cond;
        } else {
            return false;
        }
        $sql = 'UPDATE ' . $this->getTableName($table) . ' SET ' . implode(' = ? , ', $fields) . ' = ?  WHERE ' . implode(' AND ', $conds);

        $query = $this->pdo->prepare($sql);
        if ($query->execute($params) === true) {
            return true;
        } else {
            return $query;
        }
    }

    /**
     * Deletes a row/group of rows from the database
     * @param string $table
     * @param array $params
     * @param array|string $cond
     * @return bool|PDOStatement 
     */
    protected function doDel($table, array $params, $cond) {
        $conds = array();
        if (is_string($cond)) {
            $conds[] = $cond;
        } elseif (is_array($cond)) {
            $conds = $cond;
        } else {
            return false;
        }
        $sql = 'DELETE FROM ' . $this->getTableName($table) . ' WHERE ' . implode(' AND ', $conds);

        $query = $this->pdo->prepare($sql);
        if ($query->execute($params) === true) {
            return true;
        } else {
            return $query;
        }
    }

}

/**
 * Wraps around the PDO object 
 * @package Magister
 * @subpackage DB
 */
class DB {

    /**
     * The PDO object
     * @var PDO 
     */
    public $pdo;

    /**
     * The instance of the class
     * @var DB 
     */
    private static $instance;

    /**
     * Class constructor
     */
    private function __construct() {
        global $dbConfig;

        $this->pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};port={$dbConfig['port']}", $dbConfig['user'], $dbConfig['pass']);
    }

    /**
     * Clone magic function 
     */
    private function __clone() {
        
    }

    /**
     * Returns current instance of the DB object
     * @return DB 
     */
    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new DB();
        }
        return self::$instance;
    }

}

/**
 * Base Row Object.
 * @package Magister
 * @subpackage Model 
 */
abstract class RowObject {

    /**
     * The associated Model.
     * @var Model 
     */
    protected $model;

}
