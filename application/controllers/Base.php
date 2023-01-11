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
	public function list($lang, $table_name)
	{
		
		$response= db_list($lang,$table_name);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	public function show($lang, $table_name,$filter)
	{
		$response= db_show($lang, $table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	public function create($lang, $table_name)
	{
		$response= db_create($lang, $table_name);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	public function add($lang, $table_name)
	{
		$response= db_add($lang, $table_name);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
		
	}
	public function edit($lang, $table_name,$filter)
	{
		$response= db_edit($lang, $table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	public function update($lang, $table_name,$filter)
	{
		$response= db_update($lang, $table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	public function delete($lang, $table_name,$filter)
	{
		$response= db_delete($lang, $table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
	}
	
}
