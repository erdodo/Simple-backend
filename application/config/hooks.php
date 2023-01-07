<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$hook['pre_controller'][] = array(
    'class'    => 'Auths',  
    'function' => 'index', 
    'filename' => 'Auths.php',  
    'filepath' => 'hooks',
    'params'   => array()
);
