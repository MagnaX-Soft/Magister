<?php

/**
 * Helps with html generation.
 * @package Magister
 * @subpackage Helpers 
 */
class HtmlHelper {
    /**
     * Returns HTML code to include a Javascript script.
     * @param string $name name of the script
     * @return string 
     */
    public static function includeJS($name) {
        return '<script src="' . UrlHelper::asset('js', $name . '.js') . '"></script>';
    }
    
    /**
     * Returns HTML code to include a CSS stylesheet.
     * @param string $name name of the stylesheet
     * @param string $media
     * @return string 
     */
    public static function includeCSS($name, $media = 'screen, projection') {
        return '<link rel="stylesheet" href="' . UrlHelper::asset('css', $name . '.css') . '" type="text/css" media="' . $media . '">';
    }
    
    /**
     * Given the current page number and the last page number, generates 
     * pagination links and returns them. If $limit is given, it generates a 
     * field to set a custom
     * @param int $page
     * @param int $last
     * @param int $limit
     * @param array $query
     * @return string 
     */
    static function paginate($page, $last, $limit = null, array $query = array()) {
        if (!is_null($limit))
            $limitArray = array('limit' => $limit);
        else
            $limitArray = array();
        $pagination = '';
        if ($last > 1) {
            $pagination .= '<ul class="pagination">';
            if ($page > 1) {
                $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => 1))) . '">First</a></li>';
                $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $page - 1))) . '">Previous</a></li>';
            }
            if ($last < 5) {
                for ($counter = 1; $counter <= $last; $counter++) {
                    if ($counter == $page)
                        $pagination .= '<li><a class="current">' . $counter . '</a></li>';
                    else
                        $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $counter))) . '">' . $counter . '</a></li>';
                }
            } else {
                if ($page <= 3) {
                    for ($counter = 1; $counter <= 5; $counter++) {
                        if ($counter == $page)
                            $pagination .= '<li><a class="current">' . $counter . '</a></li>';
                        else
                            $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $counter))) . '">' . $counter . '</a></li>';
                    }
                    $pagination .= '<li class="dot">...</li>';
                } elseif ($page > 3 && $page < ($last - 2)) {
                    $pagination .= '<li class="dot">...</li>';
                    for ($counter = ($page - 2); $counter <= ($page + 2); $counter++) {
                        if ($counter == $page)
                            $pagination .= '<li><a class="current">' . $counter . '</a></li>';
                        else
                            $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $counter))) . '">' . $counter . '</a></li>';
                    }
                    $pagination .= '<li class="dot">...</li>';
                } else {
                    $pagination .= '<li class="dot">...</li>';
                    for ($counter = ($last - 4); $counter <= $last; $counter++) {
                        if ($counter == $page)
                            $pagination .= '<li><a class="current">' . $counter . '</a></li>';
                        else
                            $pagination .= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $counter))) . '">' . $counter . '</a></li>';
                    }
                }
            }
            if ($page < $last) {
                $pagination.= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $page + 1))) . '">Next</a></li>';
                $pagination.= '<li><a href="' . UrlHelper::query(array_merge($query, $limitArray, array('page' => $last))) . '">Last</a></li>';
            }
            $pagination.= '</ul>';
        }
        if (!is_null($limit) && ($last > 1)) {
            $pagination .= '<form action="?' . UrlHelper::query(array_merge($query)) . '" method="get" class="inline"><input type="hidden" name="page" value="' . $page . '">';
            foreach ($query as $key => $value) {
                if (is_array($value)) {
                    foreach($value as $name)
                        $pagination .= '<input type="hidden" name="' . $key . '[]" value="' . $name . '">';
                } else
                $pagination .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
            }
            $pagination .= '<label for="limit">Items per page</label> <input type="number" name="limit" value="' . $limit . '"><button>Go</button></form>';
        }
        return $pagination;
    }
}
