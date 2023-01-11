<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['default_controller'] = 'Account/token_control';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


$route['setup'] = 'Setup/index';
$route['setup/create'] = 'Setup/create';

//System control
$route['api'] = 'Account/token_control'; 


//Listeleme
$route['api/v1/(:any)/(:any)/list'] = 'Base/list/$1/$2';//Dil,tablo adı
$route['api/v1/(:any)/(:any)/show/(:any)'] = 'Base/show/$1/$2/$3';//Dil, tablo adı, filter

//Ekleme
$route['api/v1/(:any)/(:any)/add'] = 'Base/add/$1/$2';//Dil,tablo adı
$route['api/v1/(:any)/(:any)/create'] = 'Base/create/$1/$2';//Dil,tablo adı

//Güncelleme
$route['api/v1/(:any)/(:any)/update/(:any)'] = 'Base/update/$1/$2/$3';//Dil, tablo adı, filter
$route['api/v1/(:any)/(:any)/edit/(:any)'] = 'Base/edit/$1/$2/$3';//Dil, tablo adı, filter

//Silme
$route['api/v1/(:any)/(:any)/delete/(:any)'] = 'Base/delete/$1/$2/$3';//Dil, tablo adı, filter

//Giriş
$route['api/account/login'] = 'Account/login';

//Üye ol
$route['api/account/register'] = 'Account/register';

//Şifremi unuttum
$route['api/account/forgot_password'] = 'Account/forgot_password';
$route['api/account/forgot_new_password'] = 'Account/forgot_new_password';

