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
		
        $token = $this->input->request_headers()['Authorization'] ?? NULL;
		
		$data = $this->base_model->query("SELECT * FROM `users` WHERE `token` LIKE '%$token%'");
		if( empty($data) || empty($token) || strlen($token) != 32){
			$this->output->set_status_header(401)->_display();
			die();
		}
		$this->auths = $this->input->auths;
		$this->user = (array)$data;
		
    }
	public function index($lang)
	{
		//sistem ayakta mesajı
		echo 'Merhaba ';
	}
	public function list($lang, $table_name)
	{
		
		$hide_fields=(array)json_decode($this->input->auths->hide_fields??'[]')??[];
		$where = $this->input->auths->default_auths_id ?? NULL;
		
		//Default filtreler
		foreach ($where as $k => $val) {
			$str = strval(explode("=",$val['codes'])[1]);
			$filters[explode("=",$val['codes'])[0]]=eval("return $str;");	
		}
		

		//Hangi dil kullanılıyor
		$this->lang = $lang;
		$body = (array)json_decode($this->input->raw_input_stream) ?? [];
		$post = $this->input->post() ?? [];
		$get = $this->input->get() ?? [];
		$params=[];
		if(!empty($post))$params = $post;
		if(!empty($body))$params = $body;
		if(!empty($get))$params = $get;
		
		
		//Sayfalama bölümü
		$body_page = $params["page"] ?? NULL;
		$page=$body_page??1;

		//Limit bölümü
		$body_limit = $params["limit"] ?? NULL;
		$limit=$body_limit??50;

		//Sıralama bölümü
		$body_sorts = json_decode($params["sorts"]??"[]") ?? NULL;//["name=auth"];
		$sorts=[];
		foreach ($body_sorts??[] as $value) {
			$sorts[explode('=',$value)[0]]=explode('=',$value)[1] == "true"?"ASC":"DESC";
		}
		
		//Sorgulama(like) bölümü
		$body_like = json_decode($params["like"]??"[]") ?? NULL;//["name=auth"];
		$likes=[];
		foreach ($body_like??[] as $value) {
			$likes[explode('=',$value)[0]]=explode('=',$value)[1];
		}

		//Filtreleme bölümü
		$body_filters =json_decode($params["filters"]??"[]");// ["name=lists"];
		
		foreach ($body_filters??[] as $value) {
			$filters[explode('=',$value)[0]]=explode('=',$value)[1];
		}

		//Veri çekme ayarları
		$config=(object)[
			"filters"=>$filters,
			"likes"=>$likes,
			"sorts"=>$sorts,
			"limit"=>$limit,
			"page"=>$page,
		];
		$datas = $this->base_model->list($table_name,$config);
		$datas = (array)$datas;
		$all_record_count = $this->base_model->count($table_name,$config);
		$page_count = intval(ceil($all_record_count / ($body_limit ?? 50)));
		$table_info_config=(object)[
			"filters"=>[
				"name"=>$table_name
			]
		];
		$table_info = $this->base_model->show('lists',$table_info_config);

		//Tabloya ait kolonlar
		$fields = $this->getColumns('list', $table_name);
		//$enums = $this->getEnums($fields);


		//Yetkisine göre kolon gizleme
		foreach ($hide_fields as  $clm_name) {
			unset($fields[$clm_name]);
			foreach ($datas as $key => $value) {
				unset($datas[$key]->$clm_name);
			}
		}

		//Kolonlara göre gösterim ayarları
		foreach ($fields as $clm_name => $clm) {
			
			foreach ($datas as $key => $v) {
				$value = (array)$v;
				if ($clm['lang_support'] == 1) {
					//NOTE - Eğer kolonda dil desteği var ise seçili dile uygun veri döndürme fonksiyonu
					// Seçili dilde veri yoksa eğer varsayılan olara türkçe döner
					$lang_record = (array)json_decode($datas[$key]->$clm_name);
					$datas[$key]->$clm_name = empty($lang_record[$lang]) ? $lang_record['tr'] : $lang_record[$lang];
				}
				if (!empty($clm['relation_table'])) {
					//NOTE - Eğer kolonun bağlı oldupu bir tablo var ise bu fonksiyon çalışır.
					if (intval($datas[$key]->$clm_name) > 0) {
						// Eğerki kayıtta id tutuluyorsa bu fonksiyon çalışır
						$relation_columns = json_decode($clm['relation_columns']);//Hangi kolonlar isteniyor
						$gecici_id = $datas[$key]->$clm_name;//kayıt değiştirileceği için id bir değişkene atılı
						$datas[$key]->$clm_name = [];//kayıt yeniden yazılmak üzere silinir
						foreach ($relation_columns as $rc_key => $rc_value) {//istenilen bağlı kolonlar döngüye alınır
							$relation_columns_record_config=(object)[
								"filters"=>["id" => $gecici_id]
							];
							$relation_columns_record = 
								(array) $this->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır

							//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
							$datas[$key]->$clm_name['id'] = $gecici_id;
							$datas[$key]->$clm_name[$rc_value] = 
								$this->langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
						}
					} else {
						//NOTE - Eğer ki kayıtta birden fazla id (yada ikincil anahtar olarak ne seçildiyse ) varsa bu fonksiyon tetiklenir.
						if (empty($datas[$key]->$clm_name)) continue;

						//Text durumundaki array, uygun hale getirilir ve döngüye alınır.
						$degerler = json_decode($datas[$key]->$clm_name);
						if (is_array($degerler) || is_object($degerler)) {
							//Eğer gerçekten array ise burası çalışır
							$datas[$key]->$clm_name = [];
							foreach ($degerler as $r_value) {
								$relation_ids_record_config=(object)[
									"filters"=>[$clm['relation_id'] => $r_value]
								];
								$relation_ids_record = (array) $this->base_model->show($clm['relation_table'], $relation_ids_record_config);
								$datas[$key]->$clm_name[$r_value][$clm['relation_id']] = 
									!empty($relation_ids_record[$clm['relation_id']]) ? $relation_ids_record[$clm['relation_id']] : "";

								$relation_columns = json_decode($clm['relation_columns']);
								foreach ($relation_columns as $rc_key => $rc_value) {
									$relation_columns_record_config=(object)[
										"filters"=>[$clm['relation_id'] => $r_value]
									];
									$relation_columns_record = (array) $this->base_model->show($clm['relation_table'], $relation_columns_record_config);
									$datas[$key]->$clm_name[$r_value][$rc_value] = 
										$this->langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
								}
							}
						} else {
							//Eğer ki array değil text ise burası çalışır
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
				if ($clm['type'] == 'array' ) {
					$array_record = empty($datas[$key]->$clm_name)? "[]":$datas[$key]->$clm_name;
					$datas[$key]->$clm_name = json_decode($array_record) ?? $datas[$key]->$clm_name;
				}
				//TODO 'file','image'
			}
		}


		//Apiye response dönme
		$response = [
			"records" => $datas,
			"fields" => $fields,
			"all_record_count" => $all_record_count,
			"page_count"=>$page_count,
			"table_info"=>$table_info
		];

		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->_display();
		die();
	}
	public function show($lang, $table_name)
	{
		$this->lang = $lang;
		$filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		$config=(object)[
			'filters'=>$filters
		];
		$data = ($this->base_model->show($table_name,$config));
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
								(array) $this->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır

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
								$relation_ids_record = (array) $this->base_model->show($clm['relation_table'], $relation_ids_record_config);
								$data->$clm_name[$r_value][$clm['relation_id']] = 
									!empty($relation_ids_record[$clm['relation_id']]) ? $relation_ids_record[$clm['relation_id']] : "";

								$relation_columns = json_decode($clm['relation_columns']);
								foreach ($relation_columns as $rc_key => $rc_value) {
									$relation_columns_record_config=(object)[
										"filters"=>[$clm['relation_id'] => $r_value]
									];
									$relation_columns_record = (array) $this->base_model->show($clm['relation_table'], $relation_columns_record_config);
									$data->$clm_name[$r_value][$rc_value] = 
										$this->langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
								}
							}
						} else {
							//Eğer ki array değil text ise burası çalışır
							$val = $data->$clm_name;
							$data->$clm_name = [];
							$gecici4 = (array) $this->base_model->show($clm['relation_table'], [$clm['relation_id'] => $val]);


							$data->$clm_name[$clm['relation_id']] = !empty($gecici4[$clm['relation_id']]) ? $gecici4[$clm['relation_id']] : "";

							$relation_columns = json_decode($clm['relation_columns']);
							
							foreach ($relation_columns as $rc_key => $rc_value) {

								$gecici3 = (array) $this->base_model->show($clm['relation_table'], [$clm['relation_id'] => $val]);
								
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
		
		$response=[
			"data"=>$data,
			"fields"=>$fields,
			'status'=>$data?"success":"error"
		];

		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->_display();
		die();
	}
	public function create($lang, $table_name)
	{
		$this->lang = $lang;
		
		$fields= $this->getColumns('list', $table_name);
		
		
		$response=[
			"fields"=>$fields,
			'status'=>$fields?"success":"error"
		];

		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->_display();
		die();
	}
	public function add($lang, $table_name)
	{
		//ekleme isteği
		//GET, POST, FORM-DATA, BODY gibi isteklerin tamamını destekler
		$body = json_decode($this->input->raw_input_stream) ?? [];
		$post = $this->input->post() ?? [];
		$get = $this->input->get() ?? [];
		$params=[];
		if(!empty($post))$params = $post;
		if(!empty($body))$params = $body;
		if(!empty($get))$params = $get;

		

		//Hata basmalar
		$error=[];
		$error_state=FALSE;
		//Zorunluluk kontrolleri
		$columns = $this->getColumns('add',$table_name);
		foreach ($columns as $key => $value) {
			if($value['required'] == 1){
				if(empty($params[$key])){
					$error['required']=[];
					array_push($error['required'],$key);
					$error_state=TRUE;
				}
			}
		}

		if($error_state){
			$response=[
				"error"=>$error,
				"status"=>"error"
			];
			$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->set_status_header(400)
			->_display();
		die();
		}
		//Ekle
		$params['own_id']=$this->user['id'];
		$params['user_id']=$this->user['id'];
		$params['created_at']=date("y-m-d h:i:s");
		$params['updated_at']=date("y-m-d h:i:s");
		$status = $this->base_model->add($table_name,$params);
		$response=[];
		if($status){
			$config=(object)[
                "filters"=>$params,
                "sorts"=>["id=false"]
            ];
            $response['record'] =  $this->base_model->show($table_name,$config);
		}
		$response['status']=$status?"success":"error";

		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->_display();
		die();
		
	}
	public function edit($lang, $table_name,$filter)
	{
		$this->lang = $lang;
		$filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		$config=(object)[
			'filters'=>$filters
		];
		
		$data = ($this->base_model->show($table_name,$config));
		$fields= $this->getColumns('list', $table_name);
		
		$response=[
			"data"=>$data,
			"fields"=>$fields,
			'status'=>$data?"success":"error"
		];

		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->_display();
		die();
	}
	public function update($lang, $table_name,$filter)
	{
		//düzenleme isteği
		$filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		$config=(object)[
			'filters'=>$filters
		];
		
		$filtered_data = ($this->base_model->show($table_name,$config));
		//GET, POST, FORM-DATA, BODY gibi isteklerin tamamını destekler
		
		$body = (array)json_decode($this->input->raw_input_stream) ?? [];
		$post = $this->input->post() ?? [];
		$get = $this->input->get() ?? [];
		$params=[];
		if(!empty($post))$params = $post;
		if(!empty($body))$params = $body;
		if(!empty($get))$params = $get;
		
		

		//Ön güncelleme
		$updated_data=[];
		foreach ($filtered_data as $key => $value) {
			$updated_data[$key]=empty($params[$key])?$value:$params[$key];
		}
		
		//Hata basmalar
		$error=[];
		$error_state=FALSE;
		//Zorunluluk kontrolleri
		$columns = $this->getColumns('add',$table_name);
		foreach ($columns as $key => $value) {
			if($value['required'] == 1){
				if(empty($updated_data[$key])){
					$error['required']=[];
					array_push($error['required'],$key);
					$error_state=TRUE;
				}
			}
		}

		if($error_state){
			$response=[
				"error"=>$error,
				"status"=>"error"
			];
			$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->set_status_header(400)
			->_display();
		die();
		}
		//Düzenle
		$updated_data['user_id']=$this->user['id'];
		$updated_data['updated_at']=date("y-m-d h:i:s");
		$status = $this->base_model->update($table_name,$updated_data,$config);
		$response=[];
		if($status){
			$config=(object)[
                "filters"=>$updated_data,
                "sorts"=>["id=false"]
            ];
            $response['record'] =  $this->base_model->show($table_name,$config);
		}
		$response['status']=$status?"success":"error";

		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->_display();
		die();
	}
	public function delete($lang, $table_name,$filter)
	{
		//silme isteği
		$filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		$config=(object)[
			'filters'=>$filters
		];
		
		$filtered_data = ($this->base_model->show($table_name,$config));
		//GET, POST, FORM-DATA, BODY gibi isteklerin tamamını destekler
		
		
		

		//Sil
		$status = $this->base_model->delete($table_name,$config);
		$response=[];
		
		$response['status']=$status?"success":"error";

		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->_display();
		die();
	}
	public function langTranslate($data, $column)
	{
		$lang_support_config=(object)[
			"filters"=>['name' => $column]
		];
		
		$clm_data = $this->base_model->show('fields', $lang_support_config);
		$lang_support =  empty($clm_data->lang_support) ? FALSE : $clm_data->lang_support == 1 ;
		
		if ($lang_support && !empty($data)) {
			$gecici = (array)json_decode($data);
			return empty($gecici[$this->lang]) ?
			 $gecici['tr'] : $gecici[$this->lang];
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
			$field_list = json_decode($this->base_model->show('lists', $field_list_config)->fields);
			$fields = [];
			foreach ($field_list as $value) {
				$column_data_config=(object)[
					"filters"=>['name' => $value]
				];
				$column_data = $this->base_model->show('fields', $column_data_config);
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
			$field_list = json_decode($this->base_model->show('lists', $field_list_config)->fields);
			$fields = [];
			foreach ($field_list as $value) {
				$column_data_config=(object)[
					"filters"=>['name' => $value]
				];
				$column_data = $this->base_model->show('fields', $column_data_config);
				
				foreach ($column_data as $k => $v) {
					$fields[$value][$k] = $v;
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
