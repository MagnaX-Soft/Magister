<?php

/**
 * Base Row Class.
 *
 * Main class for rows.
 *
 * @package Magister
 * @subpackage Model
 */
abstract class Row {

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
     * Row constructor.
     *
     * Instanciates the associated model. If the child Row defined a
     * prepare method, calls it.
     */
    public function __construct() {
    	$this->loadModel();
    	if (method_exists($this, 'prepare'))
    		$this->prepare();
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
    				$value = $reflexModel->newInstance()->getByPrimaryKey((int) $value);
    			}
    		}
    		if (isset($this->{$key}))
    			$this->{$key} = stripslashes_deep($value);
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

    	if (!($this->model instanceof $this->modelName))
    		$this->model = new $this->modelName;
    }

    /**
     * Magic set method.
     *
     * Sets the Row's keys to the given value, or store them on the side
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
     * @return mixed
     * @throws UnknownRelationException
     */
    public function __get($name) {
        // HasOne relations
    	foreach ($this->relation as $link => $pk) {
    		list($foreignName, $field) = explode('_', $link);
    		if (ucfirst(strtolower($foreignName)) != ucfirst(strtolower($name)))
    			continue;

    		$link = strtolower($link);
    		$model = getValue($this->model->hasOne[$link], 'model', ucfirst(Inflect::pluralize($foreignName)) . 'Model');
    		$function = getValue($this->model->hasOne[$link], 'function', 'getBy' . $field);
    		$reflexModel = new ReflectionClass($model);
    		$this->{$name} = $reflexModel->newInstance()->$function($pk);
    		return $this->{$name};
    	}
        // HasMany relations
    	foreach ($this->model->hasMany as $link => $info) {
    		if (ucfirst(strtolower($link)) != ucfirst(strtolower($name)))
    			continue;

    		$link = strtolower($link);
    		$model = getValue($info, 'model', ucfirst($link) . 'Model');
    		$reflexModel = new ReflectionClass($model);
    		$modelInst = $reflexModel->newInstance();
    		$foreign_field = getValue($info, 'field', strtolower(Inflect::singularize($this->model->getClass())) . '_' . $modelInst->primaryKey);
    		$this->{$name} = $modelInst->getAll(null, null, $foreign_field . ' = ?', array($this->{$this->model->primaryKey}))->fetchAll();
    		return $this->{$name};
    	}
    	throw new UnknownRelationException('This table (' . $this->model->getClass() . ') has no relation by the name of ' . $name);
    }

    /**
     * Magic isset method.
     *
     * Uses magic get method to lazily determine if a field exists.
     *
     * @param mixed $name
     * @return boolean
     */
    public function __isset($name) {
    	try {
    		$temporary = $this->{$name};
    		return true;
    	} catch (UnknownRelationException $error) {
    		return false;
    	}
    }

}

