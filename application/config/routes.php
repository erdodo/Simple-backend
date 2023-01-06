<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['default_controller'] = 'document';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


$route['setup'] = 'Setup/index';
$route['setup/create'] = 'Setup/create';

$route['api/v1/(:any)'] = 'Base/index/$1';
$route['api/v1/(:any)/(:any)/list'] = 'Base/list/$1/$2';//Dil,tablo adı
$route['api/v1/(:any)/(:any)/add'] = 'Base/add/$1/$2';//Dil,tablo adı
$route['api/v1/(:any)/(:any)/(:any)/update'] = 'Base/update/$1/$2/$3';//Dil, tablo adı, filter
