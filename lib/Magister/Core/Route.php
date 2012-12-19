<?php

/**
 * Route class.
 * 
 * Represents a route.
 * 
 * @package Magister
 * @subpackage PHP-Router
 */
class Route {

    /**
     * URL of this Route
     * @var string
     */
    private $url;

    /**
     * Regex of this Route.
     * @var string
     */
    private $regex;

    /**
     * Accepted HTTP methods for this route
     * @var array
     */
    private $methods = array('GET', 'POST');

    /**
     * Target for this route, can be anything.
     * @var array
     */
    private $target = array('controller' => null, 'action' => null);

    /**
     * Custom parameter filters for this route
     * @var array
     */
    private $filters = array();

    /**
     * Array containing parameters passed through request URL.
     * @var array
     */
    private $parameters = array();

    /**
     * Returns the route's URL.
     * @return string 
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Returns the route's full URL.
     * @return string
     */
    public function getRequestUrl() {
        return $this->parameters['requestURL'];
    }

    /**
     * Sets the route's URL.
     * @param string $url 
     */
    public function setUrl($url) {
        // make sure that the URL is suffixed with a forward slash
        if ('/' != substr($url, -1))
            $url .= '/';

        $this->url = $url;
    }

    /**
     * Returns the route's target.
     * @return mixed 
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * Sets the route's target.
     * @param array $target 
     */
    public function setTarget(array $target) {
        if (empty($target)) {
            return;
        } elseif (is_indexed($target) && 2 == count($target)) {
            $this->target['controller'] = $target[0];
            $this->target['action'] = $target[1];
        } elseif (is_indexed($target) && 1 == count($target)) {
            // We assume that only the controller is given, the action is in the url
            $this->target['controller'] = $target[0];
        } elseif (false == is_indexed($target)) {
            if (isset($target['controller']))
                $this->target['controller'] = $target['controller'];
            if (isset($target['action']))
                $this->target['action'] = $target['action'];
        }
    }

    /**
     * MatcheTarget method.
     * 
     * Determines if the route matches the given target and parameter.
     * 
     * @param type $target
     * @param array $params
     * @return boolean
     */
    public function match($target, array $params = array()) {
        $internalParamsCount = substr_count($this->getUrl(), ':');
        $internalPresetCount = count($this->getParameters());

        if (isset($this->target['controller']) && $this->target['controller'] == $target['controller']) {
            // Route's controller defined and matches
            if (isset($this->target['action']) && $this->target['action'] == $target['action']) {
                // Route's action defined and matches
                if ($internalParamsCount == count($params)) {
                    // Route's parameters are good number
                    if ($this->parameters == $params) {
                        // Parameters pre-set
                        return true;
                    } else {
                        // Parameters are injected
                        foreach (array_keys($params) as $key) {
                            // Making sure that the right parameters have been passed.
                            if (false === strpos($this->getUrl(), ':' . $key))
                                return false;
                        }
                        return true;
                    }
                } elseif (($internalParamsCount + $internalPresetCount) == count($params)) {
                    // We have a mix of injected and pre-set
                    foreach (array_keys($params) as $key) {
                        // Making sure that the right parameters have been passed.
                        if (false === strpos($this->getUrl(), ':' . $key) && !isset($this->parameters[$key]))
                            return false;
                    }
                    return true;
                }
            } elseif (!isset($this->target['action']) || null === $this->target['action']) {
                // Route's action is injected
                if (($internalParamsCount - 1) == count($params)) {
                    // Route's parameters are good number (action injected)
                    if ($this->parameters == $params) {
                        // Parameters pre-set
                        return true;
                    } else {
                        // Parameters are injected
                        foreach (array_keys($params) as $key) {
                            // Making sure that the right parameters have been passed.
                            if (false === strpos($this->getUrl(), ':' . $key))
                                return false;
                        }
                        return true;
                    }
                } elseif (($internalParamsCount + $internalPresetCount - 1) == count($params)) {
                    // We have a mix of injected and pre-set
                    foreach (array_keys($params) as $key) {
                        // Making sure that the right parameters have been passed.
                        if (false === strpos($this->getUrl(), ':' . $key) && !isset($this->parameters[$key]))
                            return false;
                    }
                    return true;
                }
            }
        } elseif (!isset($this->target['controller']) || null === $this->target['controller']) {
            // Route's controller is injected
            if (isset($this->target['action']) && $this->target['action'] == $target['action']) {
                // Route's action defined and matches
                if (($internalParamsCount - 1) == count($params)) {
                    // Route's parameters are good number (controller injected)
                    if ($this->parameters == $params) {
                        // Parameters pre-set
                        return true;
                    } else {
                        // Parameters are injected
                        foreach (array_keys($params) as $key) {
                            // Making sure that the right parameters have been passed.
                            if (strpos($this->getUrl(), ':' . $key) === false)
                                return false;
                        }
                        return true;
                    }
                } elseif (($internalParamsCount + $internalPresetCount - 1) == count($params)) {
                    // We have a mix of injected and pre-set
                    foreach (array_keys($params) as $key) {
                        // Making sure that the right parameters have been passed.
                        if (false === strpos($this->getUrl(), ':' . $key) && !isset($this->parameters[$key]))
                            return false;
                    }
                    return true;
                }
            } elseif (!isset($this->target['action']) || null === $this->target['action']) {
                // Route's action is injected
                if (($internalParamsCount - 2) == count($params)) {
                    // Route's parameters are good number (controller + action injected)
                    if ($this->parameters == $params) {
                        // Parameters pre-set
                        return true;
                    } else {
                        // Parameters are injected
                        foreach (array_keys($params) as $key) {
                            // Making sure that the right parameters have been passed.
                            if (strpos($this->getUrl(), ':' . $key) === false)
                                return false;
                        }
                        return true;
                    }
                } elseif (($internalParamsCount + $internalPresetCount - 2) == count($params)) {
                    // We have a mix of injected and pre-set
                    foreach (array_keys($params) as $key) {
                        // Making sure that the right parameters have been passed.
                        if (false === strpos($this->getUrl(), ':' . $key) && !isset($this->parameters[$key]))
                            return false;
                    }
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns the route's HTTP methods.
     * @return array 
     */
    public function getMethods() {
        return $this->methods;
    }

    /**
     * Sets the route's HTTP methods.
     * @param array $methods 
     */
    public function setMethods(array $methods) {
        $this->methods = $methods;
    }

    /**
     * Sets the route's filters.
     * @param array $filters 
     */
    public function setFilters(array $filters) {
        $this->filters = $filters;
    }

    /**
     * Returns the route's regex.
     * @return string 
     */
    public function getRegex() {
        if (empty($this->regex))
            $this->regex = preg_replace_callback("@:(\w+)@", array(&$this, 'substituteFilter'), $this->url);

        return $this->regex;
    }

    /**
     * Returns the regex associated with a parameter.
     * @param array $matches
     * @return string 
     */
    private function substituteFilter($matches) {
        if (isset($this->filters[$matches[1]]))
            return $this->filters[$matches[1]];

        switch ($matches[1]) {
            case 'id':
                return '([\d]+)';
            case 'year':
                return '([12][\d]{3})';
            case 'month':
                return '(0[\d]|1[012])';
            case 'day':
                return '(0?[\d]|[12][\d]|3[01])';
            default:
                return '([\d\w-]+)';
        }
    }

    /**
     * Returns the route's parameters.
     * @return array 
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * Sets the route's parameters.
     * @param array $parameters 
     */
    public function setParameters(array $parameters) {
        $this->parameters = $parameters;
    }

}
