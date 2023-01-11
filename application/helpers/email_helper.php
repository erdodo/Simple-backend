<?php
defined('BASEPATH') or exit('No direct script access allowed');


    function send_email($email, $title,$message)
    {
        $ci = get_instance();
        $ci->load->library('email');
        $set_data=$ci->db->get("settings")->result();
        $settings=[];
        foreach ($set_data as $value) {
            $settings[$value->set_key]=$value->set_value;
        }
        

        $config = array();
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = $settings["smtp_host"];
        $config['smtp_user'] = $settings["smtp_user"];
        $config['smtp_pass'] = $settings['smtp_pass'];
        $config['smtp_port'] = $settings['smtp_port'];
        $config['smtp_crypto'] = "ssl";
        $config['charset'] = "UTF-8";
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = 'html';


        $ci->email->initialize($config);
        $ci->email->set_newline("\r\n");

        $ci->email->from($settings["smtp_user"], $settings["site_name"]);
        $ci->email->to($email);
        //$ci->email->priority(3);

        $ci->email->subject($title);
        $ci->email->message($message);


        if ($ci->email->send()) {
            return true;
        } else {
            return show_error($ci->email->print_debugger());
        }
    }

