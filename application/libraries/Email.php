<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Email 
{

    public function index()
    {
        return 'Test email';
    }
    public function send_email($email, $title,$message)
    {
        $this->load->library('email');
        $set_data=$this->db->get("settings")->result();
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


        $this->email->initialize($config);
        $this->email->set_newline("\r\n");

        $this->email->from($settings["smtp_user"], $settings["site_name"]);
        $this->email->to($email);
        //$this->email->priority(3);

        $this->email->subject($title);
        $this->email->message($message);


        if ($this->email->send()) {
            return true;
        } else {
            return show_error($this->email->print_debugger());
        }
    }
}
