<?php

/**
 * Router class.
 * 
 * Routing class to match request URL's against given routes and map them to a 
 * controller action.
 * 
 * @package Magister
 * @subpackage PHP-Router
 */
class Router {

    /**
     * Array that holds all Route objects.
     * 
     * @var array
     */
    private $routes = array();

    /**
     * The base url.
     * 
     * @var string
     */
    private $basePath = '';

    /**
     * The instance of the class.
     * 
     * @var Router
     */
    private static $instance;

    /**
     * Router constructor.
     */
    private function __construct() {
        
    }

    /**
     * Clone magic function.
     */
    private function __clone() {
        
    }

    /**
     * GetInstance method.
     * 
     * Returns current instance of the Router object
     * 
     * @return Router 
     */
    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    /**
     * SetBasePath method.
     * 
     * Sets the base url that will get prepended to all route urls.
     * 
     * @param string $basePath
     */
    public function setBasePath($basePath) {
        $this->basePath = (string) $basePath;
    }

    /**
     * GetBasePath method.
     * 
     * Returns the base url.
     * 
     * @return string
     */
    public function getBasePath() {
        return $this->basePath;
    }

    /**
     * PrefixURL method.
     * 
     * Prefixes url by the Router::$basePath.
     * 
     * @param string $url
     * @return string 
     */
    public function prefixURL($url) {
        return $this->basePath . $url;
    }

    /**
     * Route factory method.
     * 
     * Registers a route matching the given URL. The optionals arguments are:
     * 
     * - `target`: an array specifying the controller and action.
     * - `methods`: the HTTP methods allowed by the route. Defaults to `GET`.
     * - `filters`: custom regexes matching named parameters in the URL. Named 
     * parameters with no matching filter will default to `([\w-]+)`.
     * - `params`: pre-set the value of the route's parameters.
     * - `name`: the name of the route. REQUIRED.
     * 
     * The magic named parameters `:controller` and `:action` will set the route's 
     * target to their value, regardless of the previous target value.
     * 
     * @param string $routeUrl string
     * @param array $args Array of optional arguments.
     */
    public function map($routeUrl, array $args = array()) {
        $route = new Route();
        $route->setUrl($routeUrl);

        if (isset($args['target']))
            $route->setTarget($args['target']);

        if (isset($args['methods'])) {
            $methods = explode('|', $args['methods']);
            $route->setMethods($methods);
        }

        if (isset($args['filters']))
            $route->setFilters($args['filters']);

        if (isset($args['params']))
            $route->setParameters($args['params']);

        $this->routes[] = $route;
    }

    /**
     * Matches the current request against mapped routes.
     */
    public function matchCurrentRequest() {
        $requestMethod = (isset($_POST['_method']) && ($_method = strtoupper($_POST['_method'])) && in_array($_method, array('PUT', 'DELETE'))) ? $_method : $_SERVER['REQUEST_METHOD'];
        $requestUrl = $_SERVER['REQUEST_URI'];

        // strip GET variables from URL
        if (($pos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $pos);
        }
        return $this->match($requestUrl, $requestMethod);
    }

    /**
     * Match given request url and request method and see if a route has been 
     * defined for it.
     * If so, return route's target.
     * If not, try to extract a controller and target.
     * @param string $requestUrl
     * @param string $requestMethod
     * @return Route
     * @throws RoutingException 
     */
    public function match($requestUrl, $requestMethod = 'GET') {
        $cleanUrl = str_replace($this->getBasePath(), '', $requestUrl);
        foreach ($this->routes as $route) {
            // compare server request method with route's allowed http methods
            if (!in_array($requestMethod, $route->getMethods()))
                continue;

            // check if request url matches route regex. if not, return false.
            if (!preg_match("@^" . $route->getRegex() . "*$@i", $cleanUrl, $matches))
                continue;

            $newRoute = clone $route;

            $params = $newRoute->getParameters();

            if (preg_match_all("@:([\w-]+)@", $newRoute->getUrl(), $argument_keys)) {
                // grab array with matches
                $argument_keys = $argument_keys[1];

                // loop trough parameter names, store matching value in $params array
                foreach ($argument_keys as $key => $name) {
                    if (isset($matches[$key + 1]))
                        $params[$name] = $matches[$key + 1];
                }
            }

            $target = $newRoute->getTarget();
            if (isset($params['controller'])) {
                $target['controller'] = ucfirst(strtolower($params['controller']));
                unset($params['controller']);
            }
            if (isset($params['action'])) {
                $target['action'] = $params['action'];
                unset($params['action']);
            }
            $newRoute->setTarget($target);

            $params['requestMethod'] = $requestMethod;
            $params['requestURL'] = $requestUrl;
            $params['cleanURL'] = $cleanUrl;
            $newRoute->setParameters($params);
            return $newRoute;
        }
        throw new RoutingException("No route matching $requestMethod $requestUrl has been found.");
    }

    /**
     * URL generation method.
     * 
     * Generates a URL from the given target.
     * 
     * @param array $target The target to generate.
     * @param array $params Optional array of parameters to use in URL
     * @return string The url to the route
     * @throws UrlException 
     */
    public function generate(array $target, array $params = array()) {
        // Check that controller is complete
        if (!isset($target['controller']))
            throw new UrlException('Incomplete target was given for route generation.');

        if (!isset($target['action']))
            $target['action'] = 'index';

        foreach ($this->routes as $route) {
            if (!$route->matchTarget($target, $params))
                continue;

            $url = $route->getUrl();
            $param_keys = array();

            // replace route url with given parameters
            if ($params && preg_match_all("@:(\w+)@", $url, $param_keys)) {
                // grab array with matches
                $param_keys = $param_keys[1];
                var_dump($param_keys);
                // loop trough parameter names, store matching value in $params array
                foreach ($param_keys as $i => $key) {
                    switch($key) {
                        case 'controller':
                        case 'action':
                            $url = str_replace(':' . $key, $target[$key], $url);
                            break;
                        default:
                            $url = str_replace(':' . $key, $params[$key], $url);
                            break;
                    }
                }
            }
            return $url;
        }

        throw new UrlException("No route matching {$target['controller']}#{$target['action']}(" . urldecode(http_build_query($params)) . ") has been found.");
    }

}

/**
 * A route.
 * @package PHP-Router
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
    private $methods = array('GET');

    /**
     * Target for this route, can be anything.
     * @var array
     */
    private $target = array('controller' => null, 'action' => null);

    /**
     * The name of this route, used for reversed routing
     * @var string
     */
    private $name;

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
     * Sets the route's URL.
     * @param string $url 
     */
    public function setUrl($url) {
        $url = (string) $url;

        // make sure that the URL is suffixed with a forward slash
        if (substr($url, -1) !== '/')
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
     * @param mixed $target 
     */
    public function setTarget($target) {
        $this->target = $target;
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
    public function matchTarget($target, array $params = array()) {
        $paramCount = substr_count($this->getUrl(), ':');

        // Same controller, action and the given params count is equal to the 
        // number of required parameters
        // OR
        // Controller and action are empty and the given params count is equal 
        // to the number of required parameters
        if (($this->target['controller'] == $target['controller']
                && $this->target['action'] == $target['action']
                && $paramCount == count($params))
            || (empty($this->target['controller'])
                && empty($this->target['action'])
                && ($paramCount - 2) == count($params))) {
            foreach ($params as $key => $value) {
                // Making sure that the right parameters have been passed.
                if (strpos($this->getUrl(), ':' . $key) === false)
                    return false;
            }
            return true;
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
     * Returns the route's name.
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets the route's name.
     * @param string $name 
     */
    public function setName($name) {
        $this->name = (string) $name;
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
            case 'date':
                return '(0?[\d]|[12][\d]|3[01])';
            default:
                return '([\w-]+)';
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
