<?php

/**
 * Base Model.
 *
 * Includes generic functionality
 *
 * @package Magister
 * @subpackage Model
 */
abstract class Model implements Serializable {

    /**
     * The DataSource object.
     *
     * @var DataSource
     */
    protected $pdo;

    /**
     * The table related to the current Model.
     *
     * @var string
     */
    protected $table;

    /**
     * The class associated with single rows of the current Model.
     *
     * @var string
     */
    protected $class;

    /**
     * The name of the primary key in the associated table.
     *
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * The default order of the rows in the table.
     *
     * @var array
     */
    protected $order = array('id' => 'ASC');

    /**
     * An array of has-many table relationships.
     * @var array
     */
    public $hasMany = array();

    /**
     * An array of has-one table relationships.
     *
     * @var array
     */
    public $hasOne = array();

    /**
     * Model constructor.
     *
     * Connects to the database.
     */
    public function __construct() {
        $this->pdo = DB::getDataSource();
    }

    /**
     * Serialize method.
     *
     * There are no runtime defined properties in models, therefore, the
     * serialized representation is null.
     *
     * @access private
     * @return null
     */
    public function serialize() {
        return null;
    }

    /**
     * Unserialize method.
     *
     * Re-connects to the database upon unserialization.
     *
     * @access private
     * @param mixed $data
     */
    public function unserialize($data) {
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
     * GetTable method.
     *
     * Returns the correct table name.
     *
     * @return string
     */
    public function getTable() {
        if (empty($this->table))
            $this->table = strtolower(substr(get_class($this), 0, -5));

        return (Config::notEmpty('DB.prefix', false) ? Config::get('DB.prefix') . '_' . $this->table : $this->table;
    }

    /**
     * GetClass method.
     *
     * Gets the class associated with single rows of current Model.
     *
     * @return string
     */
    public function getClass() {
        if (empty($this->class))
            $this->class = ucfirst(Inflect::singularize(substr(get_class($this), 0, -5)));

        return $this->class;
    }

    /**
     * Magic getByField method.
     *
     * Gets a single record from the model by an arbitrary field. If many
     * records match the condition, only the first is returned.
     *
     * @param string $field
     * @param array $params
     * @return RowObject|bool
     */
    protected function getByField($field, array $params) {
        if ($this->doGetCount($params, $this->getTable() . '.' . $field . ' = ?') == 0)
            return false;

        $release = $this->doGet($params, $this->getTable() . '.' . $field . ' = ?', $this->order, 1);
        return $release->fetchObject($this->getClass());
    }

    /**
     * GetAll method.
     *
     * Gets all the rows matching a set of conditions (defaults to no
     * conditions).
     *
     * @param int $start Sets the location of the first record.
     * @param int $limit Limit the number of results returned.
     * @param string|array $cond Query conditions.
     * @param array $params Query parameters.
     * @return PDOStatement
     */
    public function getAll($start = 0, $limit = 20, $cond = null, array $params = array()) {
        if (is_null($start)) {
            return $this->doGet($params, $cond, $this->order);
        } else {
            return $this->doGet($params, $cond, $this->order, (int) $start . ', ' . (int) $limit);
        }
    }

    /**
     * GetALLCount method.
     *
     * Gets the number of rows matching a set of conditions (defaults to no
     * conditions).
     *
     * @param string|array $cond Query conditions.
     * @param array $params Query parameters.
     * @return int
     */
    public function getAllCount($cond = null, array $params = array()) {
        return $this->doGetCount($cond, $params);
    }

    /**
     * Delete method.
     *
     * Deletes a row in the database.
     *
     * @param RowObject $row
     * @return boolean
     */
    public function delete(RowObject $row) {
        if ($this->doDel(array($row->{$this->primaryKey}), $this->primaryKey . ' = ?') == 1) {
            return true;
        }
        $row->error = $this->pdo->errorInfo();
        return false;
    }

    /**
     * DoGet method.
     *
     * Gets all the columns from $table, accepts $params for prepared
     * statements, $cond for conditions, $order for ordering and $limit for
     * limits.
     *
     * @param array $params
     * @param array|string $cond
     * @param array $order
     * @param int|string $limit
     * @param array|string $select
     * @return PDOStatement
     */
    protected function doGet(array $params = null, $cond = null, array $order = array(), $limit = null, $select = array()) {
        $join = '';

        $sql = 'SELECT ';

        if (is_string($select) && $select != '*')
            $select = array($select);
        elseif ((is_string($select) && $select == '*') || empty($select)) {
            $select = array('*');
            if (!empty($this->hasOne)) {
                $select = array();
                foreach ($this->doRaw('DESCRIBE ' . $this->getTable())->fetchAll(PDO::FETCH_COLUMN) as $key) {
                    if (array_key_exists($key, $this->hasOne)) {
                        $field = getValue($this->hasOne[$key], 'field', compat_strstr($key, '_id', true));
                        $name = getValue($this->hasOne[$key], 'name', ucfirst($field));
                        $model = getValue($this->hasOne[$key], 'model', ucfirst(Inflect::pluralize($field)) . 'Model');
                        $modelObject = new $model;
                        $table = $modelObject->getTable();
                        $pk = $modelObject->primaryKey;
                        $join[$table] = $this->getTable() . '.' . $key . ' = ' . $table . '.' . $pk;
                        $select[] = $table . '.' . $pk . ' AS ' . $name . '_' . $pk;
                    } else
                        $select[] = $this->getTable() . '.' . $key;
                }
            }
        } elseif (!is_array($select))
            return false;

        $sql .= implode(', ', $select) . ' FROM ' . $this->getTable();

        if (!empty($join)) {
            foreach ($join as $table => $on) {
                $sql .= ' LEFT JOIN ' . $table . ' ON ' . $on;
            }
        }

        if (!empty($cond)) {
            if (is_string($cond))
                $cond = array($cond);
            elseif (!is_array($cond))
                return false;

            $sql .= ' WHERE ' . implode(' AND ', $cond);
        }

        if (empty($order)) {
            $order = $this->order;
        }

        $sql .= ' ORDER BY ';
        $orderStrings = array();
        foreach ($order as $field => $dir)
            $orderStrings[] = $this->getTable() . '.' . $field . ' ' . strtoupper($dir);
        $sql .= implode(', ', $orderStrings);

        if (!is_null($limit))
            $sql .= ' LIMIT ' . $limit;

        $query = $this->pdo->prepare($sql);
        if (is_null($params))
            $query->execute();
        else
            $query->execute($params);
        return $query;
    }

    /**
     * DoGetCount method.
     *
     * Counts the rows from $table, accepts $params and $cond.
     *
     * @uses Model::doGet()
     * @param array $params
     * @param array|string $cond
     * @return int
     */
    protected function doGetCount(array $params = null, $cond = null) {
        $query = $this->doGet($params, $cond, array(), null, 'COUNT(*)');
        return (int) $query->fetchColumn();
    }

    /**
     * DoPut method.
     *
     * Inserts a row into the given table.
     *
     * @param array $params
     * @param array $fields
     * @return bool|PDOStatement
     */
    protected function doPut(array $params, array $fields) {
        if (count($fields) != count($params))
            return false;

        $values = array();
        for ($i = 0; $i < count($fields); $i++) {
            $values[] = '?';
        }
        $sql = 'INSERT INTO ' . $this->getTable() . '(' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';

        $query = $this->pdo->prepare($sql);
        if ($query->execute($params) === true)
            return true;
        else
            return $query;
    }

    /**
     * DoUp method.
     *
     * Updates an existing row in the database.
     *
     * @param array $params
     * @param array $fields
     * @param array|string $cond
     * @return bool|PDOStatement
     */
    protected function doUp(array $params, array $fields, $cond) {
        if ((count($fields) > count($params)) || count($fields) < 1)
            return false;

        if (is_string($cond))
            $cond = array($cond);
        elseif (!is_array($cond))
            return false;

        $sql = 'UPDATE ' . $this->getTable() . ' SET ' . implode(' = ? , ', $fields) . ' = ?  WHERE ' . implode(' AND ', $cond);

        $query = $this->pdo->prepare($sql);
        if ($query->execute($params) === true)
            return true;
        else
            return $query;
    }

    /**
     * DoDel method.
     *
     * Deletes a row/group of rows from the database
     *
     * @param array $params
     * @param array|string $cond
     * @return bool|PDOStatement
     */
    protected function doDel(array $params, $cond) {
        if (is_string($cond))
            $cond = array($cond);
        elseif (!is_array($cond))
            return false;

        $sql = 'DELETE FROM ' . $this->getTable() . ' WHERE ' . implode(' AND ', $cond);

        $query = $this->pdo->prepare($sql);
        if ($query->execute($params) === true)
            return true;
        else
            return $query;
    }

    /**
     * DoRaw method.
     *
     * Executes a raw SQL query.
     *
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

    /**
     * DumpQuery method.
     *
     * Returns the list of queries.
     *
     * @return array
     */
    public function dumpQueries() {
        return $this->pdo->queries;
    }

}

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
            default:
                throw new UnknownDataSourceException('The ' . Config::get('DB.type') . ' datasource is not registered in this application');
        }
    }

}

/**
 * DataSource class.
 *
 * Main class for data sources. Wraps around & extends PDO class.
 *
 * @package Magister
 * @subpackage DB
 */
abstract class DataSource extends PDO {

    /**
     * The list of queries that have been run on this DataSource.
     *
     * @var array
     */
    public $queries = array();

    /**
     * Prepare method.
     *
     * Logs the statement and calls PDO::prepare.
     *
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
 *
 * Main class for row objects.
 *
 * @package Magister
 * @subpackage Model
 */
abstract class RowObject {

    /**
     * The associated Model.
     *
     * @var Model
     */
    protected $model;

    /**
     * The associated model name.
     *
     * @var string
     */
    protected $modelName;

    /**
     * Holds the row's relations
     *
     * @var array
     */
    protected $relation = array();

    /**
     * RowObject constructor.
     *
     * Instanciates the associated model.
     */
    public function __construct() {
        $this->loadModel();
    }

    /**
     * Save method.
     *
     * Inserts or update the row in the database.
     *
     * @return bool
     */
    public function save() {
        if (!empty($this->id))
            return $this->model->up($this);
        else
            return $this->model->put($this);
    }

    /**
     * Update method.
     *
     * Updates the values of the currently loaded row to new values, but does
     * not save the modifications.
     *
     * @param array $data
     */
    public function update(array $data) {
        foreach ($data as $key => $value) {
            if ($key == 'model' || $key == 'modelName')
                continue;
            foreach ($this->model->hasOne as $local => $info) {
                if (strpos($local, $key . '_') !== false) {
                    $field = getValue($info, 'field', compat_strstr($local, '_', true));
                    $model = getValue($info, 'model', ucfirst(Inflect::pluralize($field) . 'Model'));

                    $reflexModel = new ReflectionClass($model);
                    $value = $reflexModel->newInstance()->getByID((int) $value);
                }
            }
            if (isset($this->{$key}))
                $this->{$key} = $value;
        }
    }

    /**
     * Delete method.
     *
     * Deletes the current row from database.
     *
     * @return bool
     */
    public function delete() {
        return $this->model->delete($this);
    }

    /**
     * LoadModel method.
     *
     * Loads the model.
     */
    public function loadModel() {
        if (empty($this->modelName))
            $this->modelName = Inflect::pluralize(get_class($this)) . 'Model';

        if (!is_a($this->model, $this->modelName))
            $this->model = new $this->modelName;
    }

    /* TODO: Implement hasMany relationships
      foreach ($this->model->hasMany as $name => $info) {
      $model = ucfirst(Inflect::pluralize($name)) . 'Model';
      $field_name = getValue($info, 'name', Inflect::singularize($name));
      $foreign_field = getValue($info, 'field', Inflect::singularize($name) . '_' . $this->model->primaryKey);
      $reflexModel = new ReflectionClass($model);
      $modelInst = $reflexModel->newInstance();
      $responce = $modelInst->getAll(null, null, $foreign_field . ' = ?', array($this->{$this->model->primaryKey}));
      while ($row = $responce->fetchObject($modelInst->getClass()))
      $this->{strtolower($field_name)}[] = $row;
      } */

    /**
     * Magic set method.
     *
     * Sets the RowObject's keys to the given value, or store them on the side
     * if they represent a has-* relation.
     *
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value) {
        if (strpos($name, '_'))
            $this->relation[$name] = $value;
        else
            $this->{$name} = $value;
    }

    /**
     * Magic get method.
     *
     * Lazily loads the current row's relation, as they are needed.
     *
     * @param mixed $name
     * @return RowObject
     * @throws UnknownRelationException
     */
    public function __get($name) {
        foreach ($this->relation as $link => $pk) {
            list($foreign_name, $field) = explode('_', $link);
            if ($foreign_name != ucfirst($name))
                continue;

            $model = ucfirst(Inflect::pluralize($name)) . 'Model';
            $function = 'getBy' . $field;
            $reflexModel = new ReflectionClass($model);
            $this->$name = $reflexModel->newInstance()->$function($pk);
            return $this->$name;
        }
        throw new UnknownRelationException('This table has no relation by the name of ' . $name);
    }

}
