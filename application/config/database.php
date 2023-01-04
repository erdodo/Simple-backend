 <?php
	defined('BASEPATH') or exit('No direct script access allowed');
	$active_group = 'default';
	$query_builder = TRUE;

	$db['default'] = array(
		'dsn'   => '',
		'hostname' => 'localhost',
		'username' => 'root',
		'password' => '',
		'database' => 'test',
		'dbdriver' => 'mysqli',
		'dbprefix' => '',
		'pconnect' => TRUE,
		'db_debug' => TRUE,
		'cache_on' => FALSE,
		'cachedir' => '',
		'char_set' => 'utf8',
		'dbcollat' => 'utf8_general_ci',
		'swap_pre' => '',
		'encrypt' => FALSE,
		'compress' => FALSE,
		'stricton' => FALSE,
		'failover' => array()
	);
