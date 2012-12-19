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
     * The default conditions applied to a query.
     * 
     * @var array
     */
    protected $cond = array();

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
     * @return Row|bool
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

        return (Config::notEmpty('DB.prefix', false)) ? Config::get('DB.prefix') . '_' . $this->table : $this->table;
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
     * Paginate method.
     * 
     * Calculates information relative to the pagination of results.
     * 
     * @param int $page
     * @param int $limit
     * @param int $count
     * @return array In order, the current page number, the number of items per 
     * page and the last page number
     */
    public function paginate($page = 1, $limit = 25, $count = null) {
        if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0)
            $page = (int) $_GET['page'];
        if (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0)
            $limit = (int) $_GET['limit'];
        if (null === $count)
            $count = $this->getAllCount();

        $last = (int) ceil($count / $limit);

        if ($page > $last)
            $page = $last;
        
        if ($page < 1)
            $page = 1;

        return array($page, $limit, $last);
    }

    /**
     * Magic getByField method.
     * 
     * Gets a single record from the model by an arbitrary field. If many 
     * records match the condition, only the first is returned.
     * 
     * @param string $field
     * @param array $params
     * @return Row|bool
     */
    protected function getByField($field, array $params) {
        if ($this->doGetCount($params, $this->getTable() . '.' . $field . ' = ?') == 0)
            return false;

        $release = $this->doGet($params, $this->getTable() . '.' . $field . ' = ?', $this->order, 1);
        return $release->fetchObject($this->getClass());
    }
    
    /**
     * GetByPrimaryKey method.
     * 
     * Gets a single record from the model by the model's primary key.
     * 
     * @uses Model::getByField
     * @param mixed $params
     * @return Row|bool
     */
    public function getByPrimaryKey($params) {
        return $this->getByField($this->primaryKey, array($params));
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
        if (null === $start)
            return $this->doGet($params, $cond, $this->order);
        else
            return $this->doGet($params, $cond, $this->order, (int) $start . ', ' . (int) $limit);
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
        return $this->doGetCount($params, $cond);
    }
    
    /**
     * getRaw method.
     * 
     * Provides RAW sql query functionnality.
     * 
     * @param string $sql
     * @param array $params
     * @param boolean $useObject
     * @return PDOStatement
     */
    public function getRaw($sql, array $params = array(), $useObject = true) {
        return $this->doRaw($sql, $params, $useObject);
        
    }
    
    /**
     * getRawCount method.
     * 
     * Provides RAW sql query count functionnality.
     * 
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function getRawCount($sql, array $params = array()) {
        return (int) $this->doRaw($sql, $params)->fetchColumn();
    }

    /**
     * Delete method.
     * 
     * Deletes a row in the database.
     * 
     * @param Row $row
     * @return boolean 
     */
    public function delete(Row $row) {
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
        $join = array();

        $sql = 'SELECT ';

        if (is_string($select) && $select != '*')
            $select = array($select);
        elseif ((is_string($select) && $select == '*') || empty($select)) {
            $select = array('*');
            if (!empty($this->hasOne)) {
                $select = array();
                foreach ($this->doRaw('DESCRIBE ' . $this->getTable())->fetchAll(PDO::FETCH_COLUMN) as $key) {
                    if (array_key_exists($key, $this->hasOne)) {
                        $field = compat_strstr($key, '_', true);
                        $name = getValue($this->hasOne[$key], 'name', ucfirst($field));
                        $model = getValue($this->hasOne[$key], 'model', ucfirst(Inflect::pluralize($field)) . 'Model');
                        $modelObject = new $model;
                        $table = $modelObject->getTable();
                        $pk = $modelObject->primaryKey;
                        $join[$table . ' AS ' . $table . '_' . $key][] = $this->getTable() . '.' . $key . ' = ' . $table . '_' . $key . '.' . $pk;
                        $select[] = $table . '_' . $key . '.' . $pk . ' AS ' . $name . '_' . $pk;
                    } else
                        $select[] = $this->getTable() . '.' . $key;
                }
            }
        } elseif (!is_array($select))
            return false;

        $sql .= implode(', ', $select) . ' FROM ' . $this->getTable();

        if (!empty($join)) {
            foreach ($join as $table => $on) {
                $sql .= ' LEFT JOIN ' . $table . ' ON (' . implode(' AND ', $on) . ')';
            }
        }

        if (empty($cond))
            $cond = $this->cond;

        if (!empty($cond)) {
            if (is_string($cond))
                $cond = array($cond);
            elseif (!is_array($cond))
                return false;

            $sql .= ' WHERE ' . implode(' AND ', $cond);
        }

        if (empty($order))
            $order = $this->order;

        $sql .= ' ORDER BY ';
        $orderStrings = array();
        foreach ($order as $field => $dir)
            $orderStrings[] = $this->getTable() . '.' . $field . ' ' . strtoupper($dir);
        $sql .= implode(', ', $orderStrings);

        if (null !== $limit)
            $sql .= ' LIMIT ' . $limit;

        $query = $this->pdo->prepare($sql);
        $query->setFetchMode(PDO::FETCH_CLASS, $this->getClass());
        if (null === $params)
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
        if (empty($cond))
            $cond = $this->cond;
        return (int) $this->doGet($params, $cond, array(), null, 'COUNT(*)')->fetchColumn();
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
        if (true === $query->execute($params))
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
     * @param boolean $useObject
     * @return resource 
     */
    protected function doRaw($sql, array $params = array(), $useObject = true) {
        $query = $this->pdo->prepare($sql);
        if ($useObject)
            $query->setFetchMode(PDO::FETCH_CLASS, $this->getClass());
        else 
            $query->setFetchMode(PDO::FETCH_OBJ);

        if (empty($params))
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
