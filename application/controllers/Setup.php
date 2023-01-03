<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setup extends CI_Controller
{

    public function index()
    {

        $this->load->view('setup');
    }
    public function create()
    {
        $data = (object) $_GET;


        //TODO Adım 1: Tablolar tablosu 
        $this->list_setup();
        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 2: Kolonlar tablosu
        /*NOTE  
            - name:text,display:text,type:int,enums:array,minlength:int,maxlength:int,min:float,max:float,parent_table:string,relation_id:int,relation_columns:array,mask:text,regex:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */
        $this->column_setup();
        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 3: Yetkiler tablosu
        /*NOTE 
            - name:text,type:int,table_name:text,columns,hide_columns:array,auths_group_id:int
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */
        $this->auths_setup();
        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 4: Yetki grubu
        /*NOTE 
            - name:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */
        $this->auths_group_setup();
        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 5: Kullanıcılar tablosu
        /*NOTE 
            - name:text,surname:text,phone:text,email:text,password:text,settings:text,auths_group_id:int,token:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */
        $this->users_setup();
        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 6: Ayarlar Listesi
        /*NOTE 
            - name:text,logo:file,origins:array,
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */
        $this->setting_setup();
        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 7: LOGS
        /*NOTE 
            - method:text,url:text,user_ip:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */
        $this->logs_setup();
        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 8: Email Ayarları
        /*NOTE 
            - smpt_email:text,smpt_name:text,smtp_password:pass,host:text,post:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */
        $this->email_setup();
        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 9: Email Tablosu
        /*NOTE 
            - name:text,title:text,content:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */
        $this->email_table_setup();
        /*--------------------------------------------------------------------------------------------------- */
    }
    public function list_setup()
    {
        /*NOTE 
            - name:text,display:text,columns:array,before_codes:text,after_codes:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */
        $this->load->dbforge();

        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique' => TRUE,
            ),
            'display' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                //'default' => 'King of Town',
            ),
            'columns' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'default' => '[]',
            ),
            'before_codes' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'after_codes' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
        );
        $attributes = array('ENGINE' => 'InnoDB');
        $this->dbforge->create_table('table_name', FALSE, $attributes);
        //if ($this->dbforge->add_field($fields)->create_table('list')) echo 'Başarılı';
        //echo $this->dbforge->create_table('lists');
        die();

        if ($this->dbforge->add_column('lists', $fields)) echo 'Kolonlar eklendi';
    }
    public function column_setup()
    {
    }
    public function auths_setup()
    {
    }
    public function auths_group_setup()
    {
    }
    public function users_setup()
    {
    }
    public function setting_setup()
    {
    }
    public function logs_setup()
    {
    }
    public function email_setup()
    {
    }
    public function email_table_setup()
    {
    }
}
