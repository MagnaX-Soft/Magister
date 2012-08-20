<?php

/**
 * Routing class to match request URL's against given routes and map them to a controller action.
 * @package PHP-Router
 */
class Router {

    /**
     * Array that holds all Route objects
     * @var array
     */
    private $routes = array();

    /**
     * Array to store named routes in, used for reverse routing.
     * @var array 
     */
    private $namedRoutes = array();

    /**
     * The base REQUEST_URI. Gets prepended to all route url's.
     * @var string
     */
    private $basePath = '';

    /**
     * The instance of the class
     * @var Router
     */
    private static $instance;

    /**
     * Class constructor
     */
    private function __construct() {
        
    }

    /**
     * Clone magic function 
     */
    private function __clone() {
        
    }

    /**
     * Returns current instance of the Router object
     * @return Router 
     */
    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    /**
     * Set the base url - gets prepended to all route url's.
     * @param string $basePath
     */
    public function setBasePath($basePath) {
        $this->basePath = (string) $basePath;
    }

    /**
     * Prefixes url by the Router::$basePath.
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
     * `target`: an array specifying the controller and action.
     * 
     * `methods`: the HTTP methods allowed by the route. Defaults to `GET`.
     * 
     * `filters`: custom regexes matching named parameters in the URL. Named 
     * parameters with no matching filter will default to `([\w-]+)`.
     * 
     * `params`: pre-set the value of the route's parameters.
     * 
     * `name`: the name of the route. REQUIRED.
     * 
     * The magic named parameters `:controller` and `:action` will set the route's 
     * target to their value, regardless of the previous target value.
     * @param string $routeUrl string
     * @param array $args Array of optional arguments.
     */
    public function map($routeUrl, array $args = array()) {
        $route = new Route();
        $route->setUrl($this->basePath . $routeUrl);

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

        if (isset($args['name'])) {
            $route->setName($args['name']);
            if (!isset($this->namedRoutes[$route->getName()])) {
                $this->namedRoutes[$route->getName()] = $route;
            }
        }
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
        foreach ($this->routes as $route) {
            // compare server request method with route's allowed http methods
            if (!in_array($requestMethod, $route->getMethods()))
                continue;

            // check if request url matches route regex. if not, return false.
            if (!preg_match("@^" . $route->getRegex() . "*$@i", $requestUrl, $matches))
                continue;

            $params = $route->getParameters();

            if (preg_match_all("/:([\w-]+)/", $route->getUrl(), $argument_keys)) {
                // grab array with matches
                $argument_keys = $argument_keys[1];

                // loop trough parameter names, store matching value in $params array
                foreach ($argument_keys as $key => $name) {
                    if (isset($matches[$key + 1]))
                        $params[$name] = $matches[$key + 1];
                }
            }

            $target = $route->getTarget();
            if (isset($params['controller'])) {
                $target['controller'] = $params['controller'];
                unset($params['controller']);
            }
            if (isset($params['action'])) {
                $target['action'] = $params['action'];
                unset($params['action']);
            }
            $route->setTarget($target);
            
            $params['requestMethod'] = $requestMethod;
            $params['requestURL'] = $requestUrl;
            $route->setParameters($params);
            return $route;
        }
        throw new RoutingException("No route matching $requestMethod $requestUrl has been found.");
    }

    /**
     * Reverse route a named route.
     * 
     * @param string $routeName The name of the route to reverse route.
     * @param array $params Optional array of parameters to use in URL
     * @return string The url to the route
     * @throws UrlException 
     */
    public function generate($routeName, array $params = array()) {
        // Check if route exists
        if (!isset($this->namedRoutes[$routeName]))
            throw new UrlException("No route with the name $routeName has been found.");

        $route = $this->namedRoutes[$routeName];
        $url = $route->getUrl();

        $param_keys = array();

        // replace route url with given parameters
        if ($params && preg_match_all("/:(\w+)/", $url, $param_keys)) {
            // grab array with matches
            $param_keys = $param_keys[1];
            // loop trough parameter names, store matching value in $params array
            foreach ($param_keys as $i => $key) {
                if (isset($params[$key]))
                    $url = preg_replace("/:(\w+)/", $params[$key], $url, 1);
            }
        }
        return $url;
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
     * Accepted HTTP methods for this route
     * @var array
     */
    private $methods = array('GET');

    /**
     * Target for this route, can be anything.
     * @var mixed
     */
    private $target;

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
     * Array containing parameters passed through request URL
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
        return preg_replace_callback("/:(\w+)/", array(&$this, 'substituteFilter'), $this->url);
    }

    /**
     * Returns the regex associated with a parameter.
     * @param array $matches
     * @return string 
     */
    private function substituteFilter($matches) {
        if (isset($matches[1]) && isset($this->filters[$matches[1]])) {
            return $this->filters[$matches[1]];
        }
        return "([\w-]+)";
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
