<?php

/**
 * Display class.
 *
 * Handles view related functions.
 *
 * @todo Move this to templates
 * @package Magister
 * @subpackage View
 */
class Display {

    /**
     * Header method.
     *
     * Returns the HTTP status code, MIME type, charset and header HTML.
     *
     * @param string $title
     * @return string
     */
    public static function header($title) {
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
     * Footer method.
     *
     * Returns the HTML of the page footer.
     *
     * @param string $extra
     * @return string
     */
    public static function footer($extra = '') {
        return '            <div id="footer" class="span-24 last">
                <p class="small right text-right">
                    Powered by Magister
                </p>
            </div>
        </div>
        ' . ((Config::get('mode.debug', false, false)) ? Debug::display() : '') . '
        ' . $extra . '
    </body>
</html>';
    }

    /**
     * Error method.
     *
     * Outputs error message to the browser.
     *
     * @todo move this to the display class.
     * @param string $message error message
     * @param string $title title of the error page
     * @param string $http HTTP error string
     */
    public static function error($message, $title = 'Server Error', $http = '500 Internal Server Error') {
        if (!headers_sent()) {
            header('HTTP/1.1 ' . $http);
            header('Content-type: text/html; charset=utf-8');
        }
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Error</title>
                <style type="text/css"><?php echo file_get_contents(WEB_DIR . DS . 'assets' . DS . 'css' . DS . 'screen.css'); ?></style>
            </head>
            <body>
                <div class="container">
                    <div class="span-24 last append-bottom prepend-top" id="header">
                        <div class="span-24 last" id="title">
                            <h1><?php echo $title; ?></h1>
                        </div>
                        <div class="span-24 last">
                            <div class="error">
                                <strong>
                                    <p><?php echo $message; ?></p>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
        </html>
        <?php
        exit(1);
    }

}