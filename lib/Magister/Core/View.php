<?php

/**
 * View class.
 *
 * Handles view related functions.
 *
 * @package Magister
 * @subpackage View
 */
class View {

    const HTML = "text/html";
    const JSON = "application/json";
    const TEXT = "text/plain";

    /**
     * Holds a the content type.
     *
     * @var string
     */
    private $type = self::HTML;

    /**
     * Holds the view variables.
     *
     * @var array
     */
    private $vars = array();

    /**
     * Holds HTTP response statuses, sorted by code.
     *
     * @var array
     */
    private $statusCodes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        420 => 'Enhance Your Calm',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version Not Supported'
    );

    /**
     * The instance of the class.
     *
     * @var view
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
     * Returns current instance of the View object
     *
     * @return View
     */
    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new View();
        }
        return self::$instance;
    }

    /**
     * Render method.
     *
     * Renders the default layout and outputs it.
     *
     * @param int $code
     */
    public function render($code = 200) {
        header('HTTP/1.1 ' . $this->code($code));
        header('Content-type: text/html; charset=utf-8');

        echo $this->fetch('default', 'Layout');
    }

    /**
     * Fragment method.
     *
     * Renders the fragment layout and outputs it.
     *
     * @param int $code
     */
    public function fragment($code = 200) {
        header('HTTP/1.1 ' . $this->code($code));
        header('Content-type: text/html; charset=utf-8');

        echo $this->fetch('fragment', 'Layout');
    }

    /**
     * Partial method.
     *
     * Renders a partial response and outputs it. Useful for REST and AJAX.
     *
     * @param int $code the status code
     * @param string $type the content-type of the response.
     */
    public function partial($code = 200) {
        header('HTTP/1.1 ' . $this->code($code));
        header('Content-type: ' . $this->type . '; charset=utf-8');

        echo $this->fetch('partial', 'Layout');
    }

    /**
     * Redirect method.
     *
     * Redirects the brower to a new location, using the specified HTTP code.
     *
     * @param string $location
     * @param int $type
     */
    public function redirect($location, $type = 303) {
        header('HTTP/1.1 ' . $this->code($type));
        header('Location: ' . $location);
        exit(0);
    }

    /**
     * Code method.
     *
     * Returns a valid HTTP/1.1 status code string.
     *
     * @param int $code
     * @return string
     * @throws ViewException
     */
    public function code($code = 200) {
        if (!array_key_exists($code, $this->statusCodes))
            throw new ViewException(sprintf(__("'%s' is not a valid HTTP status code", 'magister'), $code));
        return $code . ' ' . $this->statusCodes[$code];
    }

    /**
     * Fetch method.
     *
     * Renders and returns the given template.
     *
     * @param string $file
     * @param string $folder
     * @return string
     */
    public function fetch($file, $folder = '') {
        $loc = (!empty($folder)) ? $folder . DS . $file . '.tpl' : $file . '.tpl';
        return $this->evaluate($loc);
    }

    /**
     * Evaluate method.
     *
     * Renders the given template and returns it.
     *
     * @param string $_file
     * @return string
     * @throws FileNotFoundException
     */
    private function evaluate($_file) {
        if (false === file_exists(APP_DIR . DS . 'Views' . DS . $_file))
            throw new FileNotFoundException('File \'' . APP_DIR . DS . 'Views' . DS . $_file . '\' not found');

        extract($this->vars);

        ob_start();
        include APP_DIR . DS . 'Views' . DS . $_file;
        return ob_get_clean();
    }

    /**
     * SetVar method.
     *
     * Sets a variable to a value for use in the templates.
     *
     * @param string $name
     * @param mixed $value
     * @param boolean $force
     */
    public function setVar($name, $value, $force = false) {
        if (true !== $force && array_key_exists($name, $this->vars))
            throw new DuplicateKeyException($name . ' has already been defined.');

        $this->vars[$name] = $value;
    }

    public function setContentType($string) {
        $this->type = $string;
    }

    /**
     * RenderError method.
     *
     * Outputs error message to the browser.
     *
     * @param Exception|string $message error message
     * @param string $title title of the error page
     * @param int $code HTTP error code
     * @param string $http HTTP error string
     */
    public function renderError($message, $title = 'Server Error', $code = '500') {
        if (!headers_sent()) {
            header('HTTP/1.1 ' . $this->code($code));
            header('Content-type: text/html; charset=utf-8');
        }

        $this->setVar('title', $title, true);
        $this->setVar('message', $message, true);
        $this->setVar('code', $code, true);

        // This clears the output buffers when they are present.
        for ($i = ob_get_level(); $i > 0; $i--)
            ob_end_clean();

        if (file_exists(APP_DIR . DS . 'Views' . DS . 'Layout' . DS . 'Errors' . DS . $code . '.tpl'))
            echo $this->evaluate('Layout' . DS . 'Errors' . DS . $code . '.tpl');
        else
            echo $this->evaluate('Layout' . DS . 'error.tpl');
        exit(1);
    }

}