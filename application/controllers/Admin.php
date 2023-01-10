<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');
class Admin extends CI_Controller
{
    public $settings=[];
    public function __construct()
    {
        parent::__construct();
        $this->load->model('base_model');
    }
    public function create_table()
    {
        $this->get_settings();

        //GET, POST, FORM-DATA, BODY gibi isteklerin tamamını destekler
		$body = json_decode($this->input->raw_input_stream) ?? [];
		$post = $this->input->post() ?? [];
		$get = $this->input->get() ?? [];
		$params=[];
		if(!empty($post))$params = $post;
		if(!empty($body))$params = $body;
		if(!empty($get))$params = $get;

        

        //Gerekli sql'lerin hazırlanması
        $db_name=$this->db->database;
        $table_name = $params['name'];
        $create_table_sql ="CREATE TABLE `$db_name`.`$table_name` ( `id` INT NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)) ENGINE = InnoDB;";
        $this->base_model->set_query($create_table_sql);
        
        $old_field=[];
        foreach (json_decode($params['fields']) as $value) {
            $field_config=[
                'filters'=>['name'=>$value]
            ];
            $field_detail = $this->base_model->show('fields',(object)$field_config);
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
                    $field_type="VARCHAR(500)";
                    break;
                case 'image':
                    $field_type="VARCHAR(500)";
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
                default:
                    # code...
                    break;
            }

            $before_field = empty($old_field)?"id":$old_field;
            
            $create_field_sql="ALTER TABLE `$table_name` ADD `$field_name` $field_type $field_required AFTER `$before_field`;";   
            $this->base_model->set_query($create_field_sql);
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
        $this->base_model->set_query($other_fields_sql);

        //Ekle
        $all_fields=json_decode($params['fields']);
        array_push($all_fields,"id","state","companies_id","description","created_at","updated_at","own_id","user_id");
        $params['fields'] =json_encode($all_fields);
		$params['own_id']=$this->settings['user']->id;
		$params['user_id']=$this->settings['user']->id;
		$params['created_at']=date("y-m-d h:i:s");
		$params['updated_at']=date("y-m-d h:i:s");
		$status = $this->base_model->add('lists',$params);
        $auths_list = ["list","create","edit","show","delete"];
        foreach ($auths_list as  $value) {
            $auths_params=[
                "name"=>$table_name."_".$value,
                "auths_type"=>$value,
                "table_name"=>$table_name,
                "auths_group"=>$this->settings['user']->auths_group,
                "state"=>1,
                "created_at"=>date("y-m-d h:i:s"),
                "updated_at"=>date("y-m-d h:i:s"),
                "own_id"=>$this->settings['user']->id,
                "user_id"=>$this->settings['user']->id,
            ];
            $status = $status? $this->base_model->add('auths',$auths_params):FALSE;
        }
		$response=[];
		if($status){
			$config=(object)[
                "filters"=>$params,
            ];
            $response['table'] =  $this->base_model->show('lists',$config);
		}
		$response['status']=$status?"success":"error";

		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->_display();
		die();
        
    }
    
    public function get_settings()
    {
        $settings=$this->base_model->list('settings',(object)[]);
        $this->settings['settings']=[];
        foreach ($settings as $value) {
            $this->settings["settings"][$value->set_key]=$value->set_value;
        }
        $token = $this->input->request_headers()['Authorization'] ?? NULL;
        if( empty($token)  || strlen($token) != 32){
            $this->output->set_status_header(401)
            ->set_output(json_encode(["error"=>"token_error"]))->_display();
            die();
        }
        
        $this->settings['user'] = ($this->base_model->query("SELECT * FROM `users` WHERE `token` LIKE '%$token%'"));
        if(empty($this->settings['user']) || $this->settings['user']->auths_group != $this->settings["settings"]['admin_id']){

            $this->output->set_status_header(401)
            ->set_output(json_encode(["error"=>"user_not_found"]))->_display();
            die();
        }

        
        
    }
}