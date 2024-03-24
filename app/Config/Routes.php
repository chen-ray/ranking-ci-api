<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');


$routes->get('rankings', 'Rankings::index');
$routes->get('index', 'Rankings::index');
$routes->get('weeks', 'Week::index');
$routes->get('publication-ids', 'Week::publicationIds');
//Route::get('weeks', [Controller::class, 'weeks']);

$routes->get('breakdowns/(:num)', 'Breakdown::index/$1');