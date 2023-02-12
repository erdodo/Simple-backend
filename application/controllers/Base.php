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
		$this->benchmark->mark('base_list_basi');
		$response= db_list($table_name);
		
		$response['benchmark']=[
			'bastan_sona'=>$this->benchmark->elapsed_time('request_time', 'request_time_end'),
			'field_list_show_oncesi'=>$this->benchmark->elapsed_time('request_time', 'field_list_show'),
			'getHideFields_oncesi'=>$this->benchmark->elapsed_time('request_time', 'getHideFields'),
			'get_columns_oncesi'=>$this->benchmark->elapsed_time('request_time', 'get_columns'),
			'get_columns_list_field_sonrasi'=>$this->benchmark->elapsed_time('request_time', 'get_columns_list_field'),
			'get_columns_field_setting_sonrasi'=>$this->benchmark->elapsed_time('request_time', 'get_columns_field_setting'),
			'table_show_oncesi'=>$this->benchmark->elapsed_time('request_time', 'table_show'),
			'table_count_oncesi'=>$this->benchmark->elapsed_time('request_time', 'table_count'),
			'getDBFilters_oncesi'=>$this->benchmark->elapsed_time('request_time', 'getDBFilters'),
			'db_list_oncesi'=>$this->benchmark->elapsed_time('request_time', 'db_list'),
			'base_list_basi'=>$this->benchmark->elapsed_time('request_time', 'base_list_basi'),
			'auths_show_basi'=>$this->benchmark->elapsed_time('request_time', 'auths_show_basi'),
			"auths_detail_basi"=>$this->benchmark->elapsed_time('request_time', 'auths_detail_basi'),
			"logs_basi"=>$this->benchmark->elapsed_time('request_time', 'logs_basi'),

		];
		$response['status'] == 'success'?res_success($response):res_error($response);
	}
	public function show($table_name,$filter)
	{
		$response= db_show($table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error($response);
	}
	public function create($table_name)
	{
		$response= db_create($table_name);
		$response['status'] == 'success'?res_success($response):res_error($response);
	}
	public function add($table_name)
	{
		$response= db_add($table_name);
		$response['status'] == 'success'?res_success($response):res_error($response);
		
	}
	public function edit($table_name,$filter)
	{
		
		$response= db_edit($table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error($response);
	}
	public function update($table_name,$filter)
	{
		$response= db_update($table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error($response);
	}
	public function delete($table_name,$filter)
	{
		$response= db_delete($table_name,$filter);
		$response['status'] == 'success'?res_success($response):res_error($response);
	}
	public function enums($table_name,$clm_name)
	{
		$response= db_enums($table_name,$clm_name);
		$response['status'] == 'success'?res_success($response):res_error($response);
	}
}
