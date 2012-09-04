<?php
/**
 * Routes registration
 * @package App
 */

$router = Router::getInstance();
$router->setBasePath(Config::get('routing.basePath'));

// Index route. Must be first
$router->map('/', array('target' => array('controller' => 'Pages', 'action' => 'view'), 'name' => 'page#index', 'params' => array('page' => 'index')));

// Regular routes.
$router->map('/:page', array('target' => array('controller' => 'Pages', 'action' => 'view'), 'name' => 'page#view'));

// This route will match any URL. Leave it last.
$router->map('/:controller/:action', array('name' => 'default'));
