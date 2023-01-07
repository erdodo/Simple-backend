<?php
defined('BASEPATH') or exit('No direct script access allowed');

function dd(...$data)
{
    $str=json_encode($data);
    echo $str ;
    die();
}