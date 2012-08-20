<?php

/**
 * Handles view related functions.
 * @todo Move this to templates
 * @package Magister
 * @subpackage View
 */
class Display {

    /**
     * Returns the HTTP status code, MIME type, charset and header HTML.
     * @param string $title
     * @return string
     */
    static function header($title) {
        header('HTTP/1.1 200 OK');
        header('Content-type: text/html; charset=utf-8');
        return '<!DOCTYPE html>
<html>
    <head>
        <title>' . $title . '</title>
        ' . HtmlHelper::includeJS('jquery') . HtmlHelper::includeJS('jquery.ui') . '
        ' . HtmlHelper::includeCSS('screen') . HtmlHelper::includeCSS('print', 'print') . '
        <!--[if lt IE 8]>
            ' . HtmlHelper::includeCSS('ie') . '
        <![endif]-->
        ' . HtmlHelper::includeCSS('jquery.ui') . '
    </head>
    <body>
        <div class="container">
            <div class="span-24 last append-bottom prepend-top" id="header">
                <div class="span-24 last" id="title">
                    <h1>' . $title . '</h1>
                </div>
            </div>
            ' . Session::getMessages();
    }

    /**
     * Returns the HTML of the page footer.
     * @param string $extra
     * @return string 
     */
    static function footer($extra = '') {
        global $debugMode;
        return '            <div id="footer" class="span-24 last">
                <p class="small right text-right">
                    Powered by Magister
                </p>
            </div>
        </div>
        ' . (($debugMode) ? Debug::display() : '') . '
        ' . $extra . '
    </body>
</html>';
    }

}