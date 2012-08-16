<?php

/**
 * Base Model. Includes generic functionality 
 * @package Magister
 * @subpackage Model
 */
abstract class Model {

    /**
     * The DataSource object
     * @var DataSource 
     */
    protected $pdo;

    /**
     * The table related to the current Model.
     * @var string 
     */
    protected $table;

    /**
     * The class associated with single rows of the current Model.
     * @var string 
     */
    protected $class;

    /**
     * The name of the primary key in the associated table.
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * Has many relationship
     * @var array 
     */
    public $hasMany = array();

    /**
     * Has one relationship
     * @var array 
     */
    public $hasOne = array();

    /**
     * Connects to the database
     */
    public function __construct() {
        $this->pdo = DB::getDataSource();
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
     * Magic __call method.
     * 
     * If the called function starts with "getBy", consider it valid and send it 
     * to Model::getByField().
     * 
     * @param string $name
     * @param array $arguments
     * @return RowObject|bool
     * @throws UndefinedMethodException 
     */
    public function __call($name, $arguments) {
        if (strpos($name, 'getBy') !== false)
            return $this->getByField(strtolower(substr($name, 5)), $arguments);

        throw new UndefinedMethodException('No method matches ' . get_class($this) . '::' . $name . '()');
    }

    /**
     * Prefixes tables.
     * @global string $dbConfig
     * @return string 
     */
    public function getTable() {
        global $dbConfig;

        return (!empty($dbConfig['prefix'])) ? $dbConfig['prefix'] . '_' . $this->table : $this->table;
    }

    /**
     * Gets the class associated with single rows of current Model.
     * @return string 
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * Gets a single record from the model by it a field. If many records match 
     * the condition, only the first is returned.
     * @param string $field
     * @param array $params
     * @return RowObject|bool
     */
    protected function getByField($field, array $params) {
        if ($this->doGetCount($params, $this->getTable() . '.' . $field . ' = ?') == 0)
            return false;

        $release = $this->doGet($params, $this->getTable() . '.' . $field . ' = ?');
        return $release->fetchObject($this->getClass());
    }

    /**
     * Get all the rows matching a set of conditions (defaults to none).
     * 
     * @param int $start Sets the location of the first record.
     * @param int $limit Limit the number of results returned.
     * @param string|array $cond Query conditions.
     * @param array $params Query parameters.
     * @return PDOStatement
     */
    public function getAll($start = 0, $limit = 20, $cond = null, $params = null) {
        if (is_null($start)) {
            return $this->doGet($params, $cond, array($this->getTable() . '.' . $this->primaryKey => 'ASC'));
        } else {
            return $this->doGet($$params, $cond, array($this->getTable() . '.' . $this->primaryKey => 'ASC'), (int) $start . ', ' . (int) $limit);
        }
    }

    /**
     * The tne number of rows matching a set of conditions (defaults to none).
     * 
     * @param string|array $cond Query conditions.
     * @param array $params Query parameters.
     * @return int 
     */
    public function getAllCount($cond = null, $params = null) {
        return $this->doGetCount($cond, $params);
    }

    /**
     * Gets all the columns from $table, accepts $params for prepared 
     * statements, $cond for conditions, $order for ordering and $limit for 
     * limits.
     * 
     * @param array $params
     * @param array|string $cond
     * @param array $order
     * @param string $limit
     * @param array|string $select
     * @return PDOStatement 
     */
    protected function doGet(array $params = null, $cond = null, array $order = null, $limit = null, $select = array()) {
        $join = '';

        $sql = 'SELECT ';
        if (is_string($select) && $select != '*') {
            $select = array($select);
        } elseif ((is_string($select) && $select == '*') || empty($select)) {
            $select = array('*');
            if (!empty($this->hasOne) && $join) {
                $select = array();
                foreach ($this->doRaw('DESCRIBE ' . $this->getTable())->fetchAll(PDO::FETCH_COLUMN) as $key) {
                    if (array_key_exists($key, $this->hasOne)) {
                        $field = getValue($this->hasOne[$key], 'field', compat_strstr($key, '_id', true));
                        $name = getValue($this->hasOne[$key], 'name', ucfirst($field));
                        $model = getValue($this->hasOne[$key], 'model', ucfirst(Inflect::pluralize($field) . 'Model'));
                        $modelObject = new $model;
                        $table = $modelObject->getTable();
                        $pk = $modelObject->primaryKey;
                        $join[$table] = $this->getTable() . '.' . $key . ' = ' . $table . '.' . $pk;
                        $select[] = $table . '.' . $pk . ' AS ' . $name . '_' . $pk;
                    } else {
                        $select[] = $this->getTable() . '.' . $key;
                    }
                }
            }
        } else {
            return false;
        }
        $sql .= implode(', ', $select) . ' FROM ' . $this->getTable();
        if (!empty($join)) {
            foreach ($join as $table => $on) {
                $sql .= ' LEFT JOIN ' . $table . ' ON ' . $on;
            }
        }
        if (!empty($cond) && is_string($cond)) {
            $sql .= ' WHERE ' . $cond;
        } elseif (!empty($cond) && is_array($cond)) {
            $sql .= ' WHERE ' . implode(' AND ', $cond);
        } elseif (empty($cond)) {
            
        } else {
            return false;
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

        var_dump($sql);
        ob_flush();
        $query = $this->pdo->prepare($sql);
        if (is_null($params))
            $query->execute();
        else
            $query->execute($params);
        return $query;
    }

    /**
     * Counts the rows from $table, accepts $params and $cond.
     * @uses Model::doGet()
     * @param array $params
     * @param array|string $cond
     * @return int
     */
    protected function doGetCount(array $params = null, $cond = null) {
        $query = $this->doGet($params, $cond, null, null, 'COUNT(*)');
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
        $sql = 'INSERT INTO ' . $this->getTable($table) . '(' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';

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
        $sql = 'UPDATE ' . $this->getTable($table) . ' SET ' . implode(' = ? , ', $fields) . ' = ?  WHERE ' . implode(' AND ', $conds);

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
        $sql = 'DELETE FROM ' . $this->getTable($table) . ' WHERE ' . implode(' AND ', $conds);

        $query = $this->pdo->prepare($sql);
        if ($query->execute($params) === true) {
            return true;
        } else {
            return $query;
        }
    }

    /**
     * Executes a raw SQL query.
     * @param string $sql
     * @param array $params
     * @return resource 
     */
    protected function doRaw($sql, array $params = null) {
        $query = $this->pdo->prepare($sql);
        if (is_null($params))
            $query->execute();
        else
            $query->execute($params);
        return $query;
    }

    public function dumpQueries() {
        return $this->pdo->queries;
    }

}

/**
 * Wraps around the PDO object 
 * @package Magister
 * @subpackage DB
 */
class DB {

    /**
     * Returns a datasource.
     *
     * @global array $dbConfig
     * @return DataSource
     * @throws UnknownDataSourceException 
     */
    public static function getDataSource() {
        global $dbConfig;

        switch ($dbConfig['type']) {
            case 'mysql':
                return new MySQLDataSource($dbConfig);
            default:
                throw new UnknownDataSourceException('The ' . $dbConfig['type'] . 'datasource is not registered in this application');
        }
    }

}

/**
 * DataSource class. 
 */
abstract class DataSource extends PDO {

    public $queries = array();

    /**
     * Logs the statement and calls PDO::prepare.
     * @param string $statement
     * @param array $driver_options
     * @return PDOStatement
     */
    public function prepare($statement, $driver_options = array()) {
        $this->queries[] = $statement;
        return parent::prepare($statement, $driver_options);
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

    /**
     * The associated model name.
     * @var string
     */
    protected $modelName;

    /**
     * Instanciates the associated model.
     */
    public function __construct() {
        $this->loadModel();
        $this->loadRelations();
    }

    /**
     * Loads the model.
     */
    public function loadModel() {
        if (empty($this->modelName))
            $this->modelName = Inflect::pluralize(get_class($this)) . 'Model';

        if (!is_a($this->model, $this->modelName))
            $this->model = new $this->modelName;
    }

    public function loadRelations() {
        if (isset($this->relationTemp) && array($this->relationTemp)) {
            foreach ($this->relationTemp as $name => $value) {
                list($foreign_name, $field) = explode('_', $name);
                foreach ($this->model->hasOne as $key => $info) {
                    $field = getValue($info, 'field', compat_strstr($key, '_', true));
                    $name = getValue($info, 'name', ucfirst($field));
                    $model = getValue($info, 'model', ucfirst(Inflect::pluralize($field) . 'Model'));

                    if ($name == $foreign_name) {
                        $reflexModel = new ReflectionClass($model);
                        $this->{strtolower($name)} = $reflexModel->newInstance()->getByID($value);
                    }
                }
            }
            unset($this->relationTemp);
        }
/* TODO: FIXME!
        foreach ($this->model->hasMany as $name => $info) {
            $model = ucfirst(Inflect::pluralize($name)) . 'Model';
            $field_name = getValue($info, 'name', Inflect::singularize($name));
            $foreign_field = getValue($info, 'field', Inflect::singularize($name) . '_' . $this->model->primaryKey);
            $reflexModel = new ReflectionClass($model);
            $modelInst = $reflexModel->newInstance();
            $responce = $modelInst->getAll(null, null, $foreign_field . ' = ?', array($this->{$this->model->primaryKey}));
            while ($row = $responce->fetchObject($modelInst->getClass()))
                $this->{strtolower($field_name)}[] = $row;
        }*/
    }

    public function __set($name, $value) {
        if (strpos($name, '_')) {
            $this->relationTemp[$name] = $value;
        } else
            $this->{$name} = ($name == 'id') ? (int) $value : $value;
    }

}
