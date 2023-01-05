<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');
class Base extends CI_Controller
{
	public $lang = 'tr';


	public function index($lang)
	{
		//sistem ayakta mesajı
		echo 'Merhaba';
	}
	public function list($lang, $table_name)
	{
		$this->lang = $lang;
		//listeleme isteği
		$this->load->model('base_model');
		
		//Sayfalama bölümü
		$body_page = NULL;
		$page=$body_page??1;

		//Limit bölümü
		$body_limit = NULL;
		$limit=$body_limit??50;

		//Sıralama bölümü
		$body_sorts =NULL;// ["name"=>FALSE,"id"=>TRUE];
		$sorts=[];
		foreach ($body_sorts??[] as $key=> $value) {
			$sorts[$key]=($value?"ASC":"DESC");
		}
		
		//Sorgulama(like) bölümü
		$body_like = NULL;//["name=auth"];
		$likes=[];
		foreach ($body_like??[] as $value) {
			$likes[explode('=',$value)[0]]=explode('=',$value)[1];
		}

		//Filtreleme bölümü
		$body_filters =NULL;// ["name=lists"];
		$filters = [];
		foreach ($body_filters??[] as $value) {
			$filters[explode('=',$value)[0]]=explode('=',$value)[1];
		}

		//Veri çekme ayarları
		$config=(object)[
			"filters"=>$filters,
			"likes"=>$likes,
			"sorts"=>$sorts,
			"limit"=>$limit,
			"page"=>$page
		];
		$datas = $this->base_model->list($table_name,$config);
		$datas = (array)$datas;

		//Tabloya ait kolonlar
		$fields = $this->getColumns('list', $table_name);
		//$enums = $this->getEnums($fields);

		//Kolonlara göre gösterim ayarları
		foreach ($fields as $clm_name => $clm) {

			foreach ($datas as $key => $v) {
				$value = (array)$v;
				if ($clm['lang_support'] == 1) {
					$gecici = (array)json_decode($datas[$key]->$clm_name);

					$datas[$key]->$clm_name = empty($gecici[$lang]) ? $gecici['tr'] : $gecici[$lang];
				}
				if (!empty($clm['relation_table'])) {
					
					if (intval($datas[$key]->$clm_name) > 0) {
						
						$relation_columns = json_decode($clm['relation_columns']);
						$gecici_id = $datas[$key]->$clm_name;
						$datas[$key]->$clm_name = [];
						foreach ($relation_columns as $rc_key => $rc_value) {
							$gecici3 = (array) $this->base_model->show($clm['relation_table'], ["id" => $gecici_id]);
							$datas[$key]->$clm_name['id'] = $gecici_id;
							$datas[$key]->$clm_name[$rc_value] = $this->langTranslate(!empty($gecici3[$rc_value]) ? $gecici3[$rc_value] : "", $rc_value);
						}
					} else {
						
						if (empty($datas[$key]->$clm_name)) continue;

						$degerler = json_decode($datas[$key]->$clm_name);

						if (is_array($degerler) || is_object($degerler)) {
							$datas[$key]->$clm_name = [];
							foreach ($degerler as $r_value) {
								$gecici2 = (array) $this->base_model->show($clm['relation_table'], [$clm['relation_id'] => $r_value]);
								$datas[$key]->$clm_name[$r_value][$clm['relation_id']] = !empty($gecici2[$clm['relation_id']]) ? $gecici2[$clm['relation_id']] : "";

								$relation_columns = json_decode($clm['relation_columns']);
								foreach ($relation_columns as $rc_key => $rc_value) {

									$gecici3 = (array) $this->base_model->show($clm['relation_table'], [$clm['relation_id'] => $r_value]);
									$datas[$key]->$clm_name[$r_value][$rc_value] = $this->langTranslate(!empty($gecici3[$rc_value]) ? $gecici3[$rc_value] : "", $rc_value);
								}
							}
						} else {
							
							$val = $datas[$key]->$clm_name;
							$datas[$key]->$clm_name = [];
							$gecici4 = (array) $this->base_model->show($clm['relation_table'], [$clm['relation_id'] => $val]);


							$datas[$key]->$clm_name[$clm['relation_id']] = !empty($gecici4[$clm['relation_id']]) ? $gecici4[$clm['relation_id']] : "";

							$relation_columns = json_decode($clm['relation_columns']);
							
							foreach ($relation_columns as $rc_key => $rc_value) {

								$gecici3 = (array) $this->base_model->show($clm['relation_table'], [$clm['relation_id'] => $val]);
								
								$datas[$key]->$clm_name[$rc_value] = $this->langTranslate(!empty($gecici3[$rc_value]) ? $gecici3[$rc_value] : "", $rc_value);
							}
						}
					}
				}
				if ($clm['type'] == 'bool') {
					$datas[$key]->$clm_name = $datas[$key]->$clm_name == 1;
				}
				if ($clm['type'] == 'pass') {
					$datas[$key]->$clm_name = '*********';
				}
				if ($clm['type'] == 'datetime') {
					$datas[$key]->$clm_name = date_format(date_create($datas[$key]->$clm_name),"d/m/y H:i:s");
				}
				//TODO 'file','image','phone','email''array'
			}
		}


		//Apiye response dönme
		$response = [
			"records" => $datas,
			"fields" => $fields,
		];

		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->_display();
		die();
	}
	public function show()
	{
		//tek veri isteği
	}
	public function create()
	{
		//ekleme isteği kolonları
	}
	public function add()
	{
		//ekleme isteği
	}
	public function edit()
	{
		//düzenleme isteği kolonları
	}
	public function update()
	{
		//düzenleme isteği
	}
	public function delete()
	{
		//silme isteği
	}
	public function langTranslate($data, $column)
	{
		$lang_support = $this->base_model->show('fields', ['name' => $column])->lang_support == 1;

		if ($lang_support && !empty($data)) {
			$gecici = (array)json_decode($data);

			return empty($gecici[$this->lang]) ? $gecici['tr'] : $gecici[$this->lang];
		} else {
			return $data;
		}
	}
	public function getColumns($type, $table_name)
	{
		//tipe göre kolon 
		if ($type == 'list') {
			//tüm kolonları getir
			$field_list = json_decode($this->base_model->show('lists', ['name' => $table_name])->fields);
			$fields = [];
			foreach ($field_list as $value) {
				$column_data = $this->base_model->show('fields', ['name' => $value]);
				
				foreach ($column_data as $k => $v) {
					$fields[$value][$k] = $this->langTranslate($v, $k);
				}
			}
			return $fields;
		}
	}
	public function getEnums($fields)
	{

		$data = array();
		foreach ($fields as $value => $value1) {
			//$value -> kolon adı
			$field = (object)$this->base_model->show('fields', ['name' => $value]);

			//Bağlı tablo varsa
			if (!empty($field->relation_table)) {

				//kolonun tablo verileri
				$list = $this->base_model->list($field->relation_table, [], '', 500);

				//gösterilecek kolonlar
				$relation_columns = json_decode($field->relation_columns);

				//tablo kolonlarını döngüye al
				foreach ($list as $record) {
					$column_record = (array)$record;

					//kullanıcının istediği kolomlar
					foreach ($relation_columns as  $column) {
						$column_data = "";

						//dil desteği var mı kontrolü
						if ($this->base_model->show('fields', ['name' => $column])->lang_support == 1) {

							$gecici = (array)json_decode($column_record[$column]);
							$column_data = empty($gecici[$this->lang]) ? $gecici['tr'] : $gecici[$this->lang];

							//$column_data =  json_decode($column_record[$column])[$this->lang];
						} else {
							$column_data = $column_record[$column];
						}

						$data[$value][$column_record[$field->relation_id]][$column] = $column_data;
					}
				}
			}
		}
		return $data;
	}
}
