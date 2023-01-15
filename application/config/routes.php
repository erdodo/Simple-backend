<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['default_controller'] = 'Account/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


$route['setup'] = 'Setup/index';
$route['setup/create'] = 'Setup/create';

//Sistem kontrol
$route['api'] = 'Account/token_control'; 


//Listeleme
$route['api/v1/(:any)/list'] = 'Base/list/$1';//Tablo adı
$route['api/v1/(:any)/show/(:any)'] = 'Base/show/$1/$2';//Tablo adı, filter

//Ekleme
$route['api/v1/(:any)/add'] = 'Base/add/$1';//tablo adı
$route['api/v1/(:any)/create'] = 'Base/create/$1';//tablo adı

//Güncelleme
$route['api/v1/(:any)/update/(:any)'] = 'Base/update/$1/$2';// tablo adı, filter
$route['api/v1/(:any)/edit/(:any)'] = 'Base/edit/$1/$2';// tablo adı, filter

//Silme
$route['api/v1/(:any)/delete/(:any)'] = 'Base/delete/$1/$2';// tablo adı, filter

//Giriş
$route['api/account/login'] = 'Account/login';

//Üye ol
$route['api/account/register'] = 'Account/register';

//Şifremi unuttum
$route['api/account/forgot_password'] = 'Account/forgot_password';
$route['api/account/forgot_new_password'] = 'Account/forgot_new_password';

//Eposta Değiştir
$route['api/account/change_email'] = 'Account/change_email';
$route['api/account/change_new_email'] = 'Account/change_new_email';

