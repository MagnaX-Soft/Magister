<?php

/**
 * Base controller. 
 * 
 * Includes generic controller logic.
 * 
 * @package Magister
 * @subpackage Controller 
 */
abstract class Controller {

    /**
     * Holds the name of the current object.
     * 
     * @var string
     */
    public $name;

    /**
     * Autoload the associated model.
     * 
     * @var bool 
     */
    protected $load = true;

    /**
     * Holds the associated model.
     * 
     * @var Model 
     */
    protected $model;

    /**
     * Holds the instance of the view object.
     * 
     * @var View
     */
    protected $view;

    /**
     * Holds the parameters for the current request.
     * 
     * @var array 
     */
    protected $params;

    /**
     * Holds the target of the current request.
     * 
     * @var array 
     */
    protected $target;

    /**
     * Controller constructor.
     * 
     * Sets the request's parameters.
     * 
     * @param array $params 
     */
    public function __construct(array $params, array $target) {
        $this->name = substr(get_class($this), 0, -10);
        $this->view = View::getInstance();
        $this->params = $params;
        $this->target = $target;

        $this->loadModel();
    }

    /**
     * LoadModel method.
     * 
     * If Controller::load is true, loads the required model. 
     */
    private function loadModel() {
        if ($this->load)
            $this->model = new $this->target['model'];
    }

    /**
     * Redirect function.
     * 
     * Redirects the client to the specified location.
     * 
     * @param string $location The redirect location.
     */
    protected function redirect($location) {
        $this->view->redirect($location);
    }

    /**
     * Render method.
     * 
     * Renders the current view and serves it to the client.
     * 
     * @param string $template
     */
    protected function render($template = null) {
        if (null === $template)
            $template = $this->target['action'];

        $this->view->setVar('content', $this->view->fetch($template, $this->name));
        $this->view->render();
    }

    /**
     * Fragment method.
     * 
     * Renders the current view as a fragment and serves it to the client.
     * 
     * @param string $template
     */
    protected function renderFragment($template = null) {
        if (null === $template)
            $template = $this->target['action'];

        $this->view->setVar('content', $this->view->fetch($template, $this->name));
        $this->view->fragment();
    }

}
