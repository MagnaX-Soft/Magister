<?php

/**
 * HtmlHelper class.
 * 
 * Helps with html generation.
 * 
 * @package Magister
 * @subpackage Helpers 
 */
class HtmlHelper {

    /**
     * Holds the name and related information for header-level assets.
     * @var array
     */
    private static $assets = array();

    /**
     * AddAsset function.
     * 
     * Adds the specified asset to the asset array.
     * 
     * @param string $type
     * @param string|array $name
     */
    private static function addAsset($type, $name, $first = true) {
        if (!isset(self::$assets[$type]))
            self::$assets[$type] = array();
        if ($first)
            array_unshift(self::$assets[$type], $name);
        else
            self::$assets[$type][] = $name;
    }

    /**
     * IncludeJS function.
     * 
     * Returns HTML code to include a Javascript script.
     * 
     * @return string 
     */
    public static function includeJS($name = null) {
        $html = '';
        if (!empty($name))
            $html .= '<script type="text/javascript" src="' . UrlHelper::asset('js', $name . '.js') . '"></script>';
        else {
            $target = Router::getInstance()->currentRoute->getTarget();
            $prefix = WEB_DIR . DS . 'assets' . DS . 'js' . DS;
            if (file_exists($prefix . APP . '.js'))
                self::addAsset('js', APP, false);
            if (file_exists($prefix . APP . '.' . strtolower($target['controller']) . '.js'))
                self::addAsset('js', APP . '.' . strtolower($target['controller']), false);
            if (file_exists($prefix . APP . '.' . strtolower($target['controller']) . '.' . $target['action'] . '.js'))
                self::addAsset('js', APP . '.' . strtolower($target['controller']) . '.' . $target['action'], false);
            foreach (self::$assets['js'] as $name)
                $html .= '<script type="text/javascript" src="' . UrlHelper::asset('js', $name . '.js') . '"></script>';
        }
        return $html;
    }

    /**
     * AddJS function.
     * 
     * Adds the specified JS file to the header.
     * 
     * @throws InvalidArgumentException
     */
    public static function addJS() {
        if (func_num_args() < 1)
            throw new InvalidArgumentException('The addJS function expects at least one argument.');

        foreach (func_get_args() as $name) {
            self::addAsset('js', $name);
        }
    }

    /**
     * IncludeCSS function.
     * 
     * Returns HTML code to include a CSS stylesheet.
     * 
     * @return string 
     */
    public static function includeCSS($name = null, $media = 'screen, projection') {
        $html = '';
        if (!empty($name))
            $html .= '<link rel="stylesheet" href="' . UrlHelper::asset('css', $name . '.css') . '" type="text/css" media="' . $media . '">';
        else {
            foreach (self::$assets['css'] as $value) {
                list($name, $media) = $value;
                $html .= '<link rel="stylesheet" href="' . UrlHelper::asset('css', $name . '.css') . '" type="text/css" media="' . $media . '">';
            }
        }
        return $html;
    }

    /**
     * AddCSS function.
     * 
     * This function works in one of 2 ways. If the first parameter is a string, 
     * it accepts at most 2 arguments. The first being the name of the css file, 
     * and the second, optional, the media of that stylesheet.  
     * If the first parameter is an array, it accepts an unlimited number of 
     * arguments. Each argument must be an array with the name of the stylesheet 
     * at the 0 index, and the media at the 1 index. Once again, the media is 
     * optional.
     */
    public static function addCSS($name, $media = 'screen, projection') {
        if (is_array($name)) {
            foreach (func_get_args() as $arg) {
                if (empty($arg[1]))
                    $arg[1] = 'screen, projection';
                self::addAsset('css', $arg);
            }
        } else
            self::addAsset('css', array($name, $media));
    }

    /**
     * Paginate method.
     * 
     * Given the current page number and the last page number, generates 
     * pagination links and returns them. If $limit is given, it generates a 
     * field to set a custom limit (items per page).
     * 
     * @param int $page
     * @param int $last
     * @param int $limit
     * @param array $query
     * @return string 
     */
    public static function paginate($page, $last, $limit = null, array $query = array()) {
        if ($last <= 1)
            return '';

        $limitArray = array();
        if (!is_null($limit))
            $limitArray = array('limit' => $limit);

        $pagination = '<ul class="pagination">';
        if ($page > 1) {
            // We are higher than page 1. Display the first & previous links.
            $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => 1))) . '">First</a></li>';
            $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $page - 1))) . '">Previous</a></li>';
        }
        if ($last < 5) {
            // There are less than 5 pages, so we are displaying them all.
            for ($counter = 1; $counter <= $last; $counter++) {
                if ($counter == $page)
                    $pagination .= '<li><a class="current">' . $counter . '</a></li>';
                else
                    $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $counter))) . '">' . $counter . '</a></li>';
            }
        } else {
            // There are more than 4 pages, so we only display a subset.
            if ($page <= 3) {
                // We are in the first 3 pages. Display 5 links, then a ellipsis.
                for ($counter = 1; $counter <= 5; $counter++) {
                    if ($counter == $page)
                        $pagination .= '<li><a class="current">' . $counter . '</a></li>';
                    else
                        $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $counter))) . '">' . $counter . '</a></li>';
                }
                $pagination .= '<li class="dot">&hellip;</li>';
            } elseif ($page >= ($last - 2)) {
                // We are in the last 3 pages. Display an ellipsis, then the 5 last links.
                $pagination .= '<li class="dot">...</li>';
                for ($counter = ($last - 4); $counter <= $last; $counter++) {
                    if ($counter == $page)
                        $pagination .= '<li><a class="current">' . $counter . '</a></li>';
                    else
                        $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $counter))) . '">' . $counter . '</a></li>';
                }
            } else {
                // We are anywhere else in the pages. Display an ellipsis, 5 links and another ellipsis.
                $pagination .= '<li class="dot">&hellip;</li>';
                for ($counter = ($page - 2); $counter <= ($page + 2); $counter++) {
                    if ($counter == $page)
                        $pagination .= '<li><a class="current">' . $counter . '</a></li>';
                    else
                        $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $counter))) . '">' . $counter . '</a></li>';
                }
                $pagination .= '<li class="dot">&hellip;</li>';
            }
        }
        if ($page < $last) {
            // We are before the last page.
            $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $page + 1))) . '">Next</a></li>';
            $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $last))) . '">Last</a></li>';
        }
        $pagination .= '</ul>';

        if (!is_null($limit)) {
            // Display the configurable limit.
            $pagination .= '<form action="?' . UrlHelper::query(array_merge($query)) . '" method="get" class="inline"><input type="hidden" name="page" value="' . $page . '">';
            foreach ($query as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $name)
                        $pagination .= '<input type="hidden" name="' . $key . '[]" value="' . $name . '">';
                } else
                    $pagination .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
            }
            $pagination .= '<label for="limit">Items per page</label> <input type="number" name="limit" value="' . $limit . '"><button>Go</button></form>';
        }
        return $pagination;
    }

    public static function htmlFull($tag, $contents, array $params = array()) {
        $string = '<' . $tag;
        foreach ($params as $key => $value) {
            if (is_int($key))
                $string .= ' ' . $value;
            else
                $string .= ' ' . $key . '="' . $value . '"';
        }
        $string .= '>' . $contents . '</' . $tag . '>';
        return $string;
    }

    public static function a($href, $text = null, array $params = array()) {
        $params['href'] = $href;
        return self::htmlFull('a', (empty($text)) ? $href : $text, $params);
    }

}