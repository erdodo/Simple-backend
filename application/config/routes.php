<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['default_controller'] = 'setup';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


$route['setup'] = 'Setup/index';
$route['setup/create'] = 'Setup/create';
