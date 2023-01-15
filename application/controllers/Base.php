<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');
class Base extends CI_Controller
{
	public $lang = 'tr';
	public $user = [];	
	public $auths = [];	
	
	public function __construct()
    {
        parent::__construct();
		$this->load->model('base_model');
		
		$this->user = (array)$this->input->user;
		$this->auths = (array)$this->input->auths;
		
    }
	public function index($lang)
	{
		//sistem ayakta mesajı
		echo 'Merhaba ';
	}
	public function list($table_name)
	{
		
		$response= db_list($table_name);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	public function show($table_name,$filter)
	{
		$response= db_show($table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	public function create($table_name)
	{
		$response= db_create($table_name);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	public function add($table_name)
	{
		$response= db_add($table_name);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
		
	}
	public function edit($table_name,$filter)
	{
		
		$response= db_edit($table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	public function update($table_name,$filter)
	{
		$response= db_update($table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	public function delete($table_name,$filter)
	{
		$response= db_delete($table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
}
