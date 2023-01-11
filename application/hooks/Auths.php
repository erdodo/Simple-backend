<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auths extends CI_Controller
{
    public function index()
    {
        $params['standart'] = $this->uri->segments[2]??NULL;
        $params['lang'] = $this->uri->segments[3]??NULL;
        $params['table'] = $this->uri->segments[4]??NULL;
        $params['fun'] = $this->uri->segments[5]??NULL;
        $params['filter'] = $this->uri->segments[6]??NULL;        
		
        $this->load->model("auths_model");
		
        if($params['standart'] =='v1'){
            $token = $this->input->request_headers()['Authorization'] ?? NULL;
            if( empty($token)  || strlen($token) != 32)res_error(["message"=>"token_error","status"=>"error"],401);
            
            $this->input->user = ($this->auths_model->query("SELECT * FROM `users` WHERE `token` LIKE '%$token%'"));
			if(empty($this->input->user))res_error(["message"=>"user_not_found","status"=>"error"],401);
			

            $fun ="";
            switch ($params['fun']) {
                case 'update':
                    $fun = 'edit';
                    break;
                case 'add':
                    $fun = 'create';
                    break;
                
                default:
                    $fun = $params['fun'];
                    break;
            }
			
            $auths_config = (object)[
                "filters"=>[
                    "auths_type" => $fun,
                    "table_name"=>$params['table'],
                    "auths_group"=>$this->input->user->auths_group
                ],
            ];
            $auths = (array) $this->auths_model->show('auths',$auths_config);
			if(empty($auths))res_error(["message"=>"auths_not_found","status"=>"error"],401);
			
			$this->input->auths = $this->detail("tr",'auths',$auths['id']);
			$this->auths_model->add('logs',[
				"method_name"=>$params['fun'],
				"url" => $this->uri->uri_string,
				"user_ip"=> $_SERVER['REMOTE_ADDR'],
				"own_id"=>1,
				"user_id"=>1
			]);

			$user_request_count_config=(object)['filters'=>['set_key'=>"user_request_count"]];
			$user_request_count = ($this->auths_model->show('settings',$user_request_count_config))->set_value;
			$filter_date= date("Y-m-d H:i:s", strtotime("-1 Minutes"));
			if($user_request_count <= $this->auths_model->set_query("SELECT * FROM `logs` WHERE `created_at` > '$filter_date' ORDER BY `id` DESC")->num_rows()){
				dd('YAVAŞ LA KAÇ TANE ALIYON');
			}
        }
        
    }
    public function detail($lang, $table_name,$id)
	{
		$this->lang = $lang;
		$config=(object)[
			'filters'=>["id"=>$id]
		];
		$data = ($this->auths_model->show($table_name,$config));
		$fields= $this->getColumns('list', $table_name);
		foreach ($fields as $clm_name => $clm) {

			
				$value = (array)$data;
				if ($clm['lang_support'] == 1) {
					//NOTE - Eğer kolonda dil desteği var ise seçili dile uygun veri döndürme fonksiyonu
					// Seçili dilde veri yoksa eğer varsayılan olara türkçe döner
					$lang_record = (array)json_decode($data->$clm_name);
					$data->$clm_name = empty($lang_record[$lang]) ? $lang_record['tr'] : $lang_record[$lang];
				}
                
				if (!empty($clm['relation_table'])) {
					//NOTE - Eğer kolonun bağlı oldupu bir tablo var ise bu fonksiyon çalışır.
                    
					if (intval($data->$clm_name) > 0) {
                        
						// Eğerki kayıtta id tutuluyorsa bu fonksiyon çalışır
						$relation_columns = json_decode($clm['relation_columns']);//Hangi kolonlar isteniyor
						$gecici_id = $data->$clm_name;//kayıt değiştirileceği için id bir değişkene atılı
						$data->$clm_name = [];//kayıt yeniden yazılmak üzere silinir
						foreach ($relation_columns as $rc_key => $rc_value) {//istenilen bağlı kolonlar döngüye alınır
							$relation_columns_record_config=(object)[
								"filters"=>["id" => $gecici_id]
							];
							$relation_columns_record = 
								(array) $this->auths_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır

							//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
							$data->$clm_name['id'] = $gecici_id;
							$data->$clm_name[$rc_value] = 
								$this->langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
						}
					} else {
						//NOTE - Eğer ki kayıtta birden fazla id (yada ikincil anahtar olarak ne seçildiyse ) varsa bu fonksiyon tetiklenir.
						if (empty($data->$clm_name)) continue;

						//Text durumundaki array, uygun hale getirilir ve döngüye alınır.
						$degerler = json_decode($data->$clm_name);
						if (is_array($degerler) || is_object($degerler)) {
							//Eğer gerçekten array ise burası çalışır
							$data->$clm_name = [];
							foreach ($degerler as $r_value) {
								$relation_ids_record_config=(object)[
									"filters"=>[$clm['relation_id'] => $r_value]
								];
								$relation_ids_record = (array) $this->auths_model->show($clm['relation_table'], $relation_ids_record_config);
								$data->$clm_name[$r_value][$clm['relation_id']] = 
									!empty($relation_ids_record[$clm['relation_id']]) ? $relation_ids_record[$clm['relation_id']] : "";

								$relation_columns = json_decode($clm['relation_columns']);
								foreach ($relation_columns as $rc_key => $rc_value) {
									$relation_columns_record_config=(object)[
										"filters"=>[$clm['relation_id'] => $r_value]
									];
									$relation_columns_record = (array) $this->auths_model->show($clm['relation_table'], $relation_columns_record_config);
									$data->$clm_name[$r_value][$rc_value] = 
										$this->langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
								}
							}
						} else {
							//Eğer ki array değil text ise burası çalışır
							$val = $data->$clm_name;
							$data->$clm_name = [];
                            $relation_columns_record_config=(object)[
                                "filters"=>[$clm['relation_id'] => $val]
                            ];
							$gecici4 = (array) $this->auths_model->show($clm['relation_table'], $relation_columns_record_config);


							$data->$clm_name[$clm['relation_id']] = !empty($gecici4[$clm['relation_id']]) ? $gecici4[$clm['relation_id']] : "";

							$relation_columns = json_decode($clm['relation_columns']);
							
							foreach ($relation_columns as $rc_key => $rc_value) {
                                $relation_columns_record_config=(object)[
                                    "filters"=>[$clm['relation_id'] => $val]
                                ];
								$gecici3 = (array) $this->auths_model->show($clm['relation_table'], $relation_columns_record_config);
								
								$data->$clm_name[$rc_value] = $this->langTranslate(!empty($gecici3[$rc_value]) ? $gecici3[$rc_value] : "", $rc_value);
							}
						}
					}
				}
				if ($clm['type'] == 'bool') {
					$data->$clm_name = $data->$clm_name == 1;
				}
				if ($clm['type'] == 'pass') {
					$data->$clm_name = '*********';
				}
				if ($clm['type'] == 'datetime') {
					$data->$clm_name = date_format(date_create($data->$clm_name),"d/m/y H:i:s");
				}
				if ($clm['type'] == 'array' ) {
					$array_record = empty($data->$clm_name)? "[]":$data->$clm_name;
					$data->$clm_name = json_decode($array_record) ?? $data->$clm_name;
				}
				//TODO 'file','image'
			
		}
		
		return $data;
	}
    public function langTranslate($data, $column)
	{
        

		$lang_support_config=(object)[
			"filters"=>['name' => $column]
		];
		
		$clm_data = $this->auths_model->show('fields', $lang_support_config);
		$lang_support =  empty($clm_data->lang_support) ? FALSE : $clm_data->lang_support == 1 ;
		
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
			$field_list_config=(object)[
				"filters"=>[
					'name' => $table_name
				]
			];
			$field_list = json_decode($this->auths_model->show('lists', $field_list_config)->fields);
			$fields = [];
			foreach ($field_list as $value) {
				$column_data_config=(object)[
					"filters"=>['name' => $value]
				];
				$column_data = $this->auths_model->show('fields', $column_data_config);
				if(!empty($column_data)){
					foreach ($column_data as $k => $v) {
	
						$fields[$value][$k] = $this->langTranslate($v, $k);
					}

				}
			}
			return $fields;
		}
		if ($type == 'add') {
			//tüm kolonları getir
			$field_list_config=(object)[
				"filters"=>[
					'name' => $table_name
				]
			];
			$field_list = json_decode($this->auths_model->show('lists', $field_list_config)->fields);
			$fields = [];
			foreach ($field_list as $value) {
				$column_data_config=(object)[
					"filters"=>['name' => $value]
				];
				$column_data = $this->auths_model->show('fields', $column_data_config);
				
				foreach ($column_data as $k => $v) {
					$fields[$value][$k] = $v;
				}
			}
			return $fields;
		}
	}
}