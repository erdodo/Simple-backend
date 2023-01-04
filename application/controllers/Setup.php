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
        $db = $this->db;
        $sql = file_get_contents(__DIR__ . '/db.sql');
        $mysqli = new mysqli($db->hostname, $db->username, $db->password, $db->database);
        $mysqli->multi_query($sql);

        //TODO Adım 1: Tablolar tablosu 
        /*NOTE 
            - name:text,display:text,columns:array,before_codes:text,after_codes:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */

        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 2: Kolonlar tablosu
        /*NOTE  
            - name:text,display:text,type:int,enums:array,minlength:int,maxlength:int,min:float,max:float,parent_table:string,relation_id:int,relation_columns:array,mask:text,regex:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */

        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 3: Yetkiler tablosu
        /*NOTE 
            - name:text,type:int,table_name:text,columns,hide_columns:array,auths_group_id:int
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */

        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 4: Yetki grubu
        /*NOTE 
            - name:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */

        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 5: Kullanıcılar tablosu
        /*NOTE 
            - name:text,surname:text,phone:text,email:text,password:text,settings:text,auths_group_id:int,token:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */

        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 6: Ayarlar Listesi
        /*NOTE 
            - name:text,logo:file,origins:array,
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */

        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 7: LOGS
        /*NOTE 
            - method:text,url:text,user_ip:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */

        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 8: Email Ayarları
        /*NOTE 
            - smpt_email:text,smpt_name:text,smtp_password:pass,host:text,post:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */

        /*--------------------------------------------------------------------------------------------------- */
        //TODO Adım 9: Email Tablosu
        /*NOTE 
            - name:text,title:text,content:text
            - state:bool,description:text,created_at:datetime,updated_at:date_time,own_id:int,user_id:int
        */

        /*--------------------------------------------------------------------------------------------------- */
        /**
         ALTER TABLE `lists` ADD `state` BOOLEAN NOT NULL DEFAULT TRUE AFTER `after_codes`, ADD `description` VARCHAR(500) NOT NULL AFTER `state`, ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `description`, ADD `updated_at` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_at`, ADD `own_id` INT NOT NULL AFTER `updated_at`, ADD `user_id` INT NOT NULL AFTER `own_id`;
         */
    }
}
