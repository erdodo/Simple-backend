<?php
defined('BASEPATH') or exit('No direct script access allowed');
    function create_table($params)
    {
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
        //$ci->get_settings();
        //Gerekli sql'lerin hazırlanması
        $db_name=$ci->db->database;
        $table_name = $params['name'];
		$table_control = $ci->base_model->phpmyadmin_query("SELECT * FROM `TABLES` WHERE `TABLE_SCHEMA` LIKE '$db_name' AND `TABLE_NAME` LIKE '$table_name'")->num_rows();
		if($table_control >0) res_error(["message"=>"table_found","status"=>"error"]);
		
        $create_table_sql ="CREATE TABLE `$db_name`.`$table_name` ( `id` INT NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)) ENGINE = InnoDB;";
        $table_create=$ci->base_model->set_query($create_table_sql);
        if(!$table_create){
            res_error(["message"=>"cannot_create_table","status"=>"error"]);
            die();
        }
        
        $old_field=[];
        foreach (json_decode($params['fields']) as $value) {
            $field_config=[
                'filters'=>['name'=>$value]
            ];
            $field_detail = $ci->base_model->show('fields',(object)$field_config);
            $field_name=$field_detail->name;
            $field_type = "";
            $field_required = $field_detail->required?"NOT NULL":"NULL";
            switch ($field_detail->type) {
                case 'number':
                    $field_type="INT";
                    break;
                case 'sort_text':
                    $field_type="VARCHAR(200)";
                    break;
                case 'long_text':
                    $field_type="TEXT";
                    break;
                case 'bool':
                    $field_type="BOOLEAN";
                    break;
                case 'file':
                    $field_type="TEXT";
                    break;
                case 'image':
                    $field_type="TEXT";
                    break;
                case 'phone':
                    $field_type="VARCHAR(50)";
                    break;
                case 'email':
                    $field_type="VARCHAR(200)";
                    break;
                case 'datetime':
                    $field_type="DATETIME";
                    break;
                case 'date':
                    $field_type="DATE";
                    break;
                case 'pass':
                    $field_type="VARCHAR(200)";
                    break;
                case 'array':
                    $field_type="TEXT";
                    break;
                case 'json':
                    $field_type="TEXT";
                    break;
				case 'float':
					$field_type="FLOAT";
					break;
                default:
                    # code...
                    break;
            }

            $before_field = empty($old_field)?"id":$old_field;
			$unique =$field_detail->benzersiz ?", ADD UNIQUE (`$field_name`)":"";
            
            $create_field_sql="ALTER TABLE `$table_name` ADD `$field_name` $field_type $field_required AFTER `$before_field` $unique;";   
            $ci->base_model->set_query($create_field_sql);
            $old_field = $field_detail->name; 
        }
        $old_field;
        $other_fields_sql="
            ALTER TABLE `$table_name` 
                ADD `state` BOOLEAN NOT NULL DEFAULT TRUE AFTER `$old_field`,
                ADD `companies_id` INT NOT NULL AFTER `state`,
                ADD `description` VARCHAR(500) NOT NULL AFTER `companies_id`,
                ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `description`, 
                ADD `updated_at` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_at`, 
                ADD `own_id` INT NOT NULL AFTER `updated_at`, 
                ADD `user_id` INT NOT NULL AFTER `own_id`;";
        $ci->base_model->set_query($other_fields_sql);

        //Ekle
        $all_fields=json_decode($params['fields']);
        array_push($all_fields,"id","state","companies_id","description","created_at","updated_at","own_id","user_id");
        $params['fields'] =json_encode($all_fields);

        $auths_list = ["list","create","edit","show","delete","enums"];
	
        foreach ($auths_list as  $value) {
            $auths_params=[
                "name"=>$table_name."_".$value,
                "auths_type"=>$value,
                "table_name"=>$table_name,
                "auths_group"=>$ci->user['auths_group'],
                "state"=>1,
                "created_at"=>date("y-m-d h:i:s"),
                "updated_at"=>date("y-m-d h:i:s"),
                "own_id"=>$ci->user['id'],
                "user_id"=>$ci->user['id'],
            ];
            $status =  $ci->base_model->add('auths',$auths_params);
			if(!$status) res_error([ "message"=>$table_name."_".$value."_add_error", "status"=>"error" ]);
		
        }
		return $params;

		
        
    }
    function edit_table($params,$table_info)
    {
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
        //$ci->get_settings();
        //Gerekli sql'lerin hazırlanması
        $db_name=$ci->db->database;
        $table_name = $table_info->name;

		$table_control = $ci->base_model->phpmyadmin_query("SELECT * FROM `TABLES` WHERE `TABLE_SCHEMA` LIKE '$db_name' AND `TABLE_NAME` LIKE '$table_name'")->num_rows();
		if($table_control <=0) res_error(["message"=>"table_not_found","status"=>"error"]);
		
        $old_field=[];

        $all_fields=json_decode($params['fields']);
        array_push($all_fields,"id","state","companies_id","description","created_at","updated_at","own_id","user_id");
        $table_columns = json_decode(ad_show('lists',"id:".$table_info->id)->fields);

        foreach ($all_fields as $value) {
            
            if(empty(array_keys($table_columns, $value))){
                echo "ekle $value";
                $field_config=[
                    'filters'=>['name'=>$value]
                ];
                $field_detail = $ci->base_model->show('fields',(object)$field_config);
                $field_name=$field_detail->name;
                $field_type = "";
                $field_required = $field_detail->required?"NOT NULL":"NULL";
                switch ($field_detail->type) {
                    case 'number':
                        $field_type="INT";
                        break;
                    case 'sort_text':
                        $field_type="VARCHAR(200)";
                        break;
                    case 'long_text':
                        $field_type="TEXT";
                        break;
                    case 'bool':
                        $field_type="BOOLEAN";
                        break;
                    case 'file':
                        $field_type="TEXT";
                        break;
                    case 'image':
                        $field_type="TEXT";
                        break;
                    case 'phone':
                        $field_type="VARCHAR(50)";
                        break;
                    case 'email':
                        $field_type="VARCHAR(200)";
                        break;
                    case 'datetime':
                        $field_type="DATETIME";
                        break;
                    case 'date':
                        $field_type="DATE";
                        break;
                    case 'pass':
                        $field_type="VARCHAR(200)";
                        break;
                    case 'array':
                        $field_type="TEXT";
                        break;
                    case 'json':
                        $field_type="TEXT";
                        break;
                    case 'float':
                        $field_type="FLOAT";
                        break;
                    default:
                        # code...
                        break;
                }
    
                $before_field = empty($old_field)?"id":$old_field;
                $unique =$field_detail->benzersiz ?", ADD UNIQUE (`$field_name`)":"";
                
                $create_field_sql="ALTER TABLE `$table_name` ADD `$field_name` $field_type $field_required AFTER `$before_field` $unique;";   
                

                $ci->base_model->set_query($create_field_sql);
                $old_field = $field_detail->name; 
            }
        }
        
        
        foreach ($table_columns as $value) {
            if(empty(array_keys($all_fields, $value))){
                echo "sil $value";
                $sil_sql="ALTER TABLE `$table_name` DROP `$value`;";
                $ci->base_model->set_query($sil_sql);
            }
        }
        $params['fields'] = json_encode($all_fields);

        
		return $params;

		
        
    }
    function delete_table($data)
    {
        $ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
        //list delete atıldığında . 
        $table_name = $data->name;
        $db_name =$ci->db->database;
        $config=(object)[
            "filters"=> ["table_name"=>$table_name ]
        ];
        $auths_list = $ci->base_model->list('auths',$config);
        if(!empty($auths_list)){
            foreach ($auths_list as $key => $value) {
                ad_delete('auths',$value->id);
            }
        }
        //yetkilerden sil
        $sql="DROP TABLE `$db_name`.`$table_name`";
        
        $ci->base_model->set_query($sql);
        //veritabanından sil
        
    }
    function add_column()
    {
        dd('add_column');
        // 
    }
    function edit_column()
    {
        dd('edit_column');
        // name ve  
    }
    function delete_column()
    {
        dd('delete_column');
    }
