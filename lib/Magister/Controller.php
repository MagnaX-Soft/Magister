<?php

/**
 * Base controller
 * @package Magister
 * @subpackage Controller 
 */

/**
 * Base controller. Includes generic controller logic.
 * @package Magister
 * @subpackage Controller 
 */
abstract class Controller {

    /**
     * Autoload the associated model.
     * @var bool 
     */
    protected $load = true;

    /**
     * Holds the associated model.
     * @var Model 
     */
    protected $model;

    /**
     * Holds the parameters for the current request.
     * @var array 
     */
    protected $params;

    /**
     * Constructor.
     * @param array $params 
     */
    public function __construct(array $params = null) {
        if (is_array($params))
            $this->setParams($params);
    }

    /**
     * Sets the request parameters.
     * @param array $params
     */
    public function setParams(array $params) {
        $this->params = $params;
    }

    /**
     * Loads the required model. 
     */
    public function loadModel() {
        $name = substr(get_class($this), 0, -10) . 'Model';
        if ($this->load)
            $this->model = new $name;
    }

}
