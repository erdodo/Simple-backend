<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');

$lang = 'tr';
$user = [];
$auths = [];
$settings = [];
$def_email = [];


//NOTE - Admin için liste
function ad_list($table_name, $page = 1)
{
	$ci = get_instance();
	$ci->load->model('base_model');
	/*-------------------------------------*/

	$config = (object) [
		"limit" => 500,
		"page" => $page,
	];
	$datas = $ci->base_model->list($table_name, $config);

	return $datas;
}

/*-------------------------------------------------------------------------*/
//NOTE -  Admin tek veri gösterme isteği
function ad_show($table_name, $filter)
{
	$ci = get_instance();
	$ci->load->model('base_model');
	$ci->user = (array) $ci->input->user;
	$ci->auths = (array) $ci->input->auths;
	/*-------------------------------------*/

	//Default filtreler
	$filters2 = (intval($filter) > 0) ? ["id" => $filter] : [explode(":", $filter)[0] => explode(":", $filter)[1]];
	$config = (object) [
		'filters' => $filters2
	];
	$data = ($ci->base_model->show($table_name, $config));
	return $data;
}
/*-------------------------------------------------------------------------*/

//NOTE - Klasik veri ekleme isteği
function ad_add($table_name, $params)
{
	$ci = get_instance();
	$ci->load->model('base_model');
	$ci->user = (array) $ci->input->user;
	$ci->auths = (array) $ci->input->auths;
	/*-------------------------------------*/


	//Ekle
	$params['companies_id'] = $ci->user['companies_id'];
	$params['own_id'] = $ci->user['id'];
	$params['user_id'] = $ci->user['id'];
	$params['created_at'] = date("y-m-d h:i:s");
	$params['updated_at'] = date("y-m-d h:i:s");
	$status = $ci->base_model->add($table_name, $params);
	$response = [];
	if ($status) {
		$config = (object) [
			"filters" => $params,
			"sorts" => ["id=false"]
		];
		$response['record'] = $ci->base_model->show($table_name, $config);
	}

	return $response['record'];
}

function ad_update($table_name, $filter, $params)
{
	$ci = get_instance();
	$ci->load->model('base_model');
	$ci->user = (array) $ci->input->user;
	$ci->auths = (array) $ci->input->auths;
	/*-------------------------------------*/
	header('Content-Type: text/html; charset=utf-8		');
	//düzenleme isteği
	$filters = (intval($filter) > 0) ? ["id" => $filter] : [explode(":", $filter)[0] => explode(":", $filter)[1]];
	//Default filtreler
	$config = (object) [
		'filters' => $filters
	];

	$filtered_data = ($ci->base_model->show($table_name, $config));
	if (empty($filtered_data)) {
		$response = [
			"error" => "data_not_found",
			"status" => "error"
		];
		return $response;
		die();
	}

	//Ön güncelleme
	$updated_data = [];
	foreach ($filtered_data as $key => $value) {
		if (is_null($params[$key] ?? NULL) ?? FALSE) {
			$updated_data[$key] = $value;
		} else {
			$updated_data[$key] = $params[$key];
		}
	}






	//Düzenle
	$updated_data['companies_id'] = $ci->user['companies_id'];
	$updated_data['user_id'] = $ci->user['id'];
	$updated_data['updated_at'] = date("y-m-d h:i:s");
	$status = $ci->base_model->update($table_name, $updated_data, $config);
	$response = [];
	if ($status) {
		$config = (object) [
			"filters" => $updated_data,
			"sorts" => ["id=false"]
		];
		$response['record'] = $ci->base_model->show($table_name, $config);
	}

	return $response['record'];
}
function ad_delete($table_name, $filter)
{
	$ci = get_instance();
	$ci->load->model('base_model');
	$ci->user = (array) $ci->input->user;
	$ci->auths = (array) $ci->input->auths;
	/*-------------------------------------*/


	$filters = (intval($filter) > 0) ? ["id" => $filter] : [explode(":", $filter)[0] => explode(":", $filter)[1]];
	$config = (object) [
		'filters' => $filters
	];
	$data = ($ci->base_model->show($table_name, $config));

	if (empty($data)) {
		return (["message" => "data_not_found", "status" => "error"]);
	}


	//silme isteği
	$config = (object) [
		'filters' => ["id" => $data->id]
	];

	//Sil
	$status = $ci->base_model->delete($table_name, $config);
	$response = [];

	$response['status'] = $status ? "success" : "error";

	return $response;
}

function get_user()
{
	$ci = get_instance();
	$ci->load->model('base_model');

	$token = $ci->input->request_headers()['token'] ?? NULL;
	if (empty($token))
		$token = $ci->input->get('token') ?? NULL;
	if (empty($token) || strlen($token) != 32)
		res_error(["message" => "token_error", "status" => "error"], 401);

	$ci->input->user = (array) ($ci->base_model->query("SELECT * FROM `users` WHERE `token` LIKE '%$token%'"));
	if (empty($ci->input->user))
		res_error(["message" => "user_not_found", "status" => "error"], 401);

	$ci->input->auths_group = ad_show('auths_group', 'id:' . $ci->input->user['auths_group']);
	$ci->input->companies = ad_show('companies', 'id:' . $ci->input->user['companies_id']);
}