 <?php
	defined('BASEPATH') or exit('No direct script access allowed');
	$active_group = 'default';
	$query_builder = TRUE;

 	$hostname="localhost";
	$username='erdogany_admin';
 	$password='Erdogan112233.';
	$database='erdogany_veritabani';

	$db['default'] = array(
		'dsn'   => '',
		'hostname' => $hostname,
		'username' => $username,
		'password' => $password,
		'database' => $database,
		'dbdriver' => 'mysqli',
		'dbprefix' => '',
		'pconnect' => TRUE,
		'db_debug' => FALSE,
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
	
	$db['phpmyadmin'] = array(
		'dsn'   => '',
		'hostname' => $hostname,
		'username' => $username,
		'password' => $password,
		'database' => "information_schema",
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