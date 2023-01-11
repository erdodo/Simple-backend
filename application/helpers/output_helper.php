<?php
defined('BASEPATH') or exit('No direct script access allowed');

    function res_success($response)
    {
        $ci = get_instance();
        $ci->output
			->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response))
			->set_status_header(200)
			->_display();
            die();
    }
    function res_error($response,$code=400)
    {
        $ci = get_instance();
        $ci->output
			->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response))
			->set_status_header($code)
			->_display();
            die();
    }