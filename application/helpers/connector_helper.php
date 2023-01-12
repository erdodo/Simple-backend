<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');

	$lang = 'tr';
	$user = [];	
	$auths = [];	
	$settings=[];
	$def_email=[];

	function dd(...$data)
	{
		$str=json_encode($data);
		echo $str ;
		die();
	}

	//NOTE - Klasik listeleme isteği
	function db_list($lang, $table_name)
	{
		$ci =& get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		
		$hide_fields=(array)json_decode($ci->auths['hide_fields']??'[]')??[];
		
		//Default filtreler
		$where = $ci->auths['default_auths_id'] ?? NULL;
		$where = empty($where)?[]:$where;
		$filters=[];
		foreach ($where as $k => $val) {

			$str = strval(explode("=",$val['codes'])[1]);
			$filters[explode("=",$val['codes'])[0]]=eval("return $str;");	
		}
		

		//Hangi dil kullanılıyor
		$ci->lang = $lang;
		$body = (array)json_decode($ci->input->raw_input_stream) ?? [];
		$post = $ci->input->post() ?? [];
		$get = $ci->input->get() ?? [];
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
		$datas = $ci->base_model->list($table_name,$config);
		if(empty($datas))res_error(["message"=>"data_not_found","status"=>"error"]);

		$datas = (array)$datas;
		$all_record_count = $ci->base_model->count($table_name,$config);
		$page_count = intval(ceil($all_record_count / ($body_limit ?? 50)));
		$table_info_config=(object)[
			"filters"=>[
				"name"=>$table_name
			]
		];
		$table_info = $ci->base_model->show('lists',$table_info_config);

		//Tabloya ait kolonlar
		$fields = get_columns( $table_name);
		//$enums = $ci->getEnums($fields);


		//Yetkisine göre kolon gizleme
		foreach ($hide_fields as  $clm_name) {
			unset($fields[$clm_name]);
			foreach ($datas as $key => $value) {
				unset($datas[$key]->$clm_name);
			}
		}

		//Kolonlara göre gösterim ayarları
		field_list_show($fields,$datas);


		//Apiye response dönme
		$response = [
			"records" => $datas,
			"fields" => $fields,
			"all_record_count" => $all_record_count,
			"page_count"=>$page_count,
			"table_info"=>$table_info,
			"status"=>"success"
		];
			return $response;
		
	}
	/*-------------------------------------------------------------------------*/
	//NOTE - Klasik tek veri gösterme isteği
	function db_show($lang, $table_name,$filter)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		$hide_fields=(array)json_decode($ci->auths['hide_fields']??'[]')??[];
		
		//Default filtreler
		$where = $ci->auths['default_auths_id'] ?? [];
		$filters=[];
		foreach ($where as $k => $val) {
			$str = strval(explode("=",$val['codes'])[1]);
			$filters[explode("=",$val['codes'])[0]]=eval("return $str;");	
		}
		$ci->lang = $lang;
		$filters2 = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		$config=(object)[
			'filters'=>array_merge($filters,$filters2)
		];
		$data = ($ci->base_model->show($table_name,$config));
		if(empty($data))res_error(["message"=>"data_not_found","status"=>"error"]);

		$fields= get_columns( $table_name);

		//Yetkisine göre kolon gizleme
		foreach ($hide_fields as  $clm_name) {
			unset($fields[$clm_name]);
			unset($data->$clm_name);
			
		}
		

		field_show_show($fields,$data);
		
		$response=[
			"data"=>$data,
			"fields"=>$fields,
			'status'=>$data?"success":"error"
		];

		return $response;
	}
	/*-------------------------------------------------------------------------*/
	//NOTE - Listeleme isteğinde verinin daha düzenli gözükmesi için gerekli ayarlar burada yapılıe
	function field_list_show($fields,$datas)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
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
								(array) $ci->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır

							//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
							$datas[$key]->$clm_name['id'] = $gecici_id;
							$datas[$key]->$clm_name[$rc_value] = 
								langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
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
								$relation_ids_record = (array) $ci->base_model->show($clm['relation_table'], $relation_ids_record_config);
								$datas[$key]->$clm_name[$r_value][$clm['relation_id']] = 
									!empty($relation_ids_record[$clm['relation_id']]) ? $relation_ids_record[$clm['relation_id']] : "";

								$relation_columns = json_decode($clm['relation_columns']);
								foreach ($relation_columns as $rc_key => $rc_value) {
									$relation_columns_record_config=(object)[
										"filters"=>[$clm['relation_id'] => $r_value]
									];
									$relation_columns_record = (array) $ci->base_model->show($clm['relation_table'], $relation_columns_record_config);
									$datas[$key]->$clm_name[$r_value][$rc_value] = 
										langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
								}
							}
						} else {
							//Eğer ki array değil text ise burası çalışır
							$val = $datas[$key]->$clm_name;
							$datas[$key]->$clm_name = [];
							$gecici4 = (array) $ci->base_model->show($clm['relation_table'], [$clm['relation_id'] => $val]);


							$datas[$key]->$clm_name[$clm['relation_id']] = !empty($gecici4[$clm['relation_id']]) ? $gecici4[$clm['relation_id']] : "";

							$relation_columns = json_decode($clm['relation_columns']);
							
							foreach ($relation_columns as $rc_key => $rc_value) {

								$gecici3 = (array) $ci->base_model->show($clm['relation_table'], [$clm['relation_id'] => $val]);
								
								$datas[$key]->$clm_name[$rc_value] = langTranslate(!empty($gecici3[$rc_value]) ? $gecici3[$rc_value] : "", $rc_value);
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
				if ($clm['type'] == 'file' || $clm['type'] == 'image' ) {
					$ci->load->helper('url');
					if(!empty($datas[$key]->$clm_name)){
						$files = json_decode($datas[$key]->$clm_name);
						$datas[$key]->$clm_name=[];
						foreach ($files as $file_key => $yakala) {
							
							$datas[$key]->$clm_name[$file_key]=[];
							$datas[$key]->$clm_name[$file_key]['full_link'] = empty($yakala->full)?'':   base_url().'public/uploads/'.$yakala->full  ;
							$datas[$key]->$clm_name[$file_key]['mini_link'] = empty($yakala->mini)?'':   base_url().'public/uploads/'.$yakala->mini  ;
							$datas[$key]->$clm_name[$file_key]['full'] = empty($yakala->full)?'':   $yakala->full  ;
							$datas[$key]->$clm_name[$file_key]['mini'] = empty($yakala->mini)?'':   $yakala->mini  ;
						}
						
					}
					

				}
				//TODO 'file','image'
			}
		}
	}
	/*-------------------------------------------------------------------------*/
	//NOTE - Tek veri gösterim isteğinde verinin daha düzenli gözükmesi için gerekli ayarlar burada yapılıe
	function field_show_show($fields,$data)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		if(!empty($data)){
			foreach ($fields as $clm_name => $clm) {

				$value = (array)$data;
				if ($clm['lang_support'] == 1) {
					//NOTE - Eğer kolonda dil desteği var ise seçili dile uygun veri döndürme fonksiyonu
					// Seçili dilde veri yoksa eğer varsayılan olara türkçe döner
					$lang_record = (array)json_decode($data->$clm_name);
					$data->$clm_name = empty($lang_record[$ci->lang]) ? $lang_record['tr'] : $lang_record[$ci->lang];
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
								(array) $ci->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır

							//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
							$data->$clm_name['id'] = $gecici_id;
							$data->$clm_name[$rc_value] = 
								langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
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
								$relation_ids_record = (array) $ci->base_model->show($clm['relation_table'], $relation_ids_record_config);
								$data->$clm_name[$r_value][$clm['relation_id']] = 
									!empty($relation_ids_record[$clm['relation_id']]) ? $relation_ids_record[$clm['relation_id']] : "";

								$relation_columns = json_decode($clm['relation_columns']);
								foreach ($relation_columns as $rc_key => $rc_value) {
									$relation_columns_record_config=(object)[
										"filters"=>[$clm['relation_id'] => $r_value]
									];
									$relation_columns_record = (array) $ci->base_model->show($clm['relation_table'], $relation_columns_record_config);
									$data->$clm_name[$r_value][$rc_value] = 
										langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
								}
							}
						} else {
							//Eğer ki array değil text ise burası çalışır
							$val = $data->$clm_name;
							$data->$clm_name = [];
							$gecici4 = (array) $ci->base_model->show($clm['relation_table'], [$clm['relation_id'] => $val]);


							$data->$clm_name[$clm['relation_id']] = !empty($gecici4[$clm['relation_id']]) ? $gecici4[$clm['relation_id']] : "";

							$relation_columns = json_decode($clm['relation_columns']);
							
							foreach ($relation_columns as $rc_key => $rc_value) {

								$gecici3 = (array) $ci->base_model->show($clm['relation_table'], [$clm['relation_id'] => $val]);
								
								$data->$clm_name[$rc_value] = langTranslate(!empty($gecici3[$rc_value]) ? $gecici3[$rc_value] : "", $rc_value);
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
				if ($clm['type'] == 'file' || $clm['type'] == 'image' ) {
					$ci->load->helper('url');
					if(!empty($data->$clm_name)){
						$files = json_decode($data->$clm_name);
						$data->$clm_name=[];
						foreach ($files as $file_key => $yakala) {
							
							$data->$clm_name[$file_key]=[];
							$data->$clm_name[$file_key]['full_link'] = empty($yakala->full)?'':   base_url().'public/uploads/'.$yakala->full  ;
							$data->$clm_name[$file_key]['mini_link'] = empty($yakala->mini)?'':   base_url().'public/uploads/'.$yakala->mini  ;
							$data->$clm_name[$file_key]['full'] = empty($yakala->full)?'':   $yakala->full  ;
							$data->$clm_name[$file_key]['mini'] = empty($yakala->mini)?'':   $yakala->mini  ;
						}
						
					}
					

				}
			
		}
		}
	}
	/*-------------------------------------------------------------------------*/
	//NOTE - Klasik ekleme kolonları çekme isteği
	function db_create($lang, $table_name)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/

		$ci->lang = $lang;
		
		$fields= get_columns($table_name);
		//Yetkisine göre kolon gizleme
		$hide_fields=(array)json_decode($ci->auths['hide_fields']??'[]')??[];
		array_push($hide_fields,'id','own_id','user_id','created_at','updated_at');
		foreach ($hide_fields as  $clm_name) {
			unset($fields[$clm_name]);
		}
		
		$response=[
			"fields"=>$fields,
			'status'=>$fields?"success":"error"
		];

		return $response;
	}
	//NOTE - Klasik veri ekleme isteği
	function db_add($lang, $table_name)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/

		
		//ekleme isteği
		//GET, POST, FORM-DATA, BODY gibi isteklerin tamamını destekler
		$body = json_decode($ci->input->raw_input_stream) ?? [];
		$post = $ci->input->post() ?? [];
		$get = $ci->input->get() ?? [];
		$params=[];
		if(!empty($post))$params = $post;
		if(!empty($body))$params = $body;
		if(!empty($get))$params = $get;
		
		
		
		//Hata basmalar
		$required=[];
		$error_state=FALSE;
		//Zorunluluk kontrolleri
		$columns = get_columns($table_name);
		foreach ($columns as $key => $value) {
			if($value['required'] == 1){
				if(empty($params[$key])){
					$required=[];
					array_push($required,$key);
					$error_state=TRUE;
				}
			}
			if($value['type']=='file'){
				$params[$key]=json_encode(upload_file($key));
			}
		}
		//Olmayan kolon gelirse sil
		foreach ($params as $key => $value) {
			if(empty($columns[$key])){
				unset($params[$key]);
			}
		}
		//Gizlenecek kolonları veritabanına gönderme
		$hide_fields=(array)json_decode($ci->auths['hide_fields']??'[]')??[];
		array_push($hide_fields,'id','own_id','user_id','created_at','updated_at');
		foreach ($hide_fields as  $clm_name) {
			unset($params[$clm_name]);
		}
		if($error_state){
			$response=[
				"required"=>$required,
				"message"=>"required_error",
				"status"=>"error"
			];
			res_error($response);
		die();
		}
		if($table_name == 'lists')$params= create_table($params);
		//Ekle
		$params['companies_id']=$ci->user['companies_id'];
		$params['own_id']=$ci->user['id'];
		$params['user_id']=$ci->user['id'];
		$params['created_at']=date("y-m-d h:i:s");
		$params['updated_at']=date("y-m-d h:i:s");
		
		$status = $ci->base_model->add($table_name,$params);
		$response=[];
		if($status){
			$config=(object)[
                "filters"=>$params,
                "sorts"=>["id=false"]
            ];
            $response['record'] =  $ci->base_model->show($table_name,$config);
		}
		$response['status']=$status?"success":"error";

		return $response;
		
	}
	function db_edit($lang, $table_name,$filter)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		$ci->lang = $lang;
		$filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];

		//Default filtreler
		$where = $ci->auths['default_auths_id'] ?? [];
		foreach ($where as $k => $val) {
			$str = strval(explode("=",$val['codes'])[1]);
			$filters[explode("=",$val['codes'])[0]]=eval("return $str;");	
		}
		$config=(object)[
			'filters'=>$filters
		];
		
		$data = ($ci->base_model->show($table_name,$config));
		if(empty($data))res_error(["message"=>"data_not_found","status"=>"error"]);

		$fields= get_columns( $table_name);
		//Yetkisine göre kolon gizleme
		$hide_fields=(array)json_decode($ci->auths['hide_fields']??'[]')??[];
		array_push($hide_fields,'id','own_id','user_id','created_at','updated_at');
		foreach ($hide_fields as  $clm_name) {
			unset($fields[$clm_name]);
		}
		field_edit_show($fields,$data);
		$response=[
			"data"=>$data,
			"fields"=>$fields,
			'status'=>$data?"success":"error"
		];

		return $response;
	}
	//NOTE - Tek veri gösterim isteğinde verinin daha düzenli gözükmesi için gerekli ayarlar burada yapılıe
	function field_edit_show($fields,$data)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		if(!empty($data)){
			foreach ($fields as $clm_name => $clm) {

				$value = (array)$data;
				if ($clm['lang_support'] == 1) {
					//NOTE - Eğer kolonda dil desteği var ise seçili dile uygun veri döndürme fonksiyonu
					// Seçili dilde veri yoksa eğer varsayılan olara türkçe döner
					$lang_record = (array)json_decode($data->$clm_name);
					$data->$clm_name = empty($lang_record[$ci->lang]) ? $lang_record['tr'] : $lang_record[$ci->lang];
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
								(array) $ci->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır

							//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
							$data->$clm_name['id'] = $gecici_id;
							$data->$clm_name[$rc_value] = 
								langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
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
								$relation_ids_record = (array) $ci->base_model->show($clm['relation_table'], $relation_ids_record_config);
								$data->$clm_name[$r_value][$clm['relation_id']] = 
									!empty($relation_ids_record[$clm['relation_id']]) ? $relation_ids_record[$clm['relation_id']] : "";

								$relation_columns = json_decode($clm['relation_columns']);
								foreach ($relation_columns as $rc_key => $rc_value) {
									$relation_columns_record_config=(object)[
										"filters"=>[$clm['relation_id'] => $r_value]
									];
									$relation_columns_record = (array) $ci->base_model->show($clm['relation_table'], $relation_columns_record_config);
									$data->$clm_name[$r_value][$rc_value] = 
										langTranslate(!empty($relation_columns_record[$rc_value]) ? $relation_columns_record[$rc_value] : "", $rc_value);
								}
							}
						} else {
							//Eğer ki array değil text ise burası çalışır
							$val = $data->$clm_name;
							$data->$clm_name = [];
							$gecici4 = (array) $ci->base_model->show($clm['relation_table'], [$clm['relation_id'] => $val]);


							$data->$clm_name[$clm['relation_id']] = !empty($gecici4[$clm['relation_id']]) ? $gecici4[$clm['relation_id']] : "";

							$relation_columns = json_decode($clm['relation_columns']);
							
							foreach ($relation_columns as $rc_key => $rc_value) {

								$gecici3 = (array) $ci->base_model->show($clm['relation_table'], [$clm['relation_id'] => $val]);
								
								$data->$clm_name[$rc_value] = langTranslate(!empty($gecici3[$rc_value]) ? $gecici3[$rc_value] : "", $rc_value);
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
				if ($clm['type'] == 'file' || $clm['type'] == 'image' ) {
					$ci->load->helper('url');
					if(!empty($data->$clm_name)){
						$files = json_decode($data->$clm_name);
						unset($data->$clm_name);
						$clm_name="old_".$clm_name;
						$data->$clm_name=[];
						foreach ($files as $file_key => $yakala) {
							
							$data->$clm_name[$file_key]=[];
							$data->$clm_name[$file_key]['full_link'] = empty($yakala->full)?'':   base_url().'public/uploads/'.$yakala->full  ;
							$data->$clm_name[$file_key]['mini_link'] = empty($yakala->mini)?'':   base_url().'public/uploads/'.$yakala->mini  ;
							$data->$clm_name[$file_key]['full'] = empty($yakala->full)?'':   $yakala->full  ;
							$data->$clm_name[$file_key]['mini'] = empty($yakala->mini)?'':   $yakala->mini  ;
						}
						
					}
					

				}
			
		}
		}
	}
	/*-------------------------------------------------------------------------*/
	function db_update($lang, $table_name,$filter)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		//düzenleme isteği
		$filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		//Default filtreler
		$where = $ci->auths['default_auths_id'] ?? [];
		foreach ($where as $k => $val) {
			$str = strval(explode("=",$val['codes'])[1]);
			$filters[explode("=",$val['codes'])[0]]=eval("return $str;");	
		}
		$config=(object)[
			'filters'=>$filters
		];
		
		$filtered_data = ($ci->base_model->show($table_name,$config));
		if(empty($filtered_data))res_error(["message"=>"data_not_found","status"=>"error"]);

		//GET, POST, FORM-DATA, BODY gibi isteklerin tamamını destekler
		
		$body = (array)json_decode($ci->input->raw_input_stream) ?? [];
		$post = $ci->input->post() ?? [];
		$get = $ci->input->get() ?? [];
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
		$required=[];
		$error_state=FALSE;
		//Zorunluluk kontrolleri
		$columns = get_columns($table_name);
		foreach ($columns as $key => $value) {
			if($value['required'] == 1){
				if(empty($updated_data[$key])){
					$required=[];
					array_push($required,$key);
					$error_state=TRUE;
				}
			}
			if($value['type']=='file'){
				$old_file =empty($updated_data[$key])?[]: json_decode($updated_data[$key]);
				$all_file=array_merge($old_file ,upload_file($key));
				
				$updated_data[$key]=json_encode($all_file);
			}
		}


		//Olmayan kolon gelirse sil
		foreach ($params as $key => $value) {
			if(empty($columns[$key])){
				unset($params[$key]);
			}
		}
		//Gizlenecek kolonları veritabanına gönderme
		$hide_fields=(array)json_decode($ci->auths['hide_fields']??'[]')??[];
		array_push($hide_fields,'id','own_id','user_id','created_at','updated_at');
		foreach ($hide_fields as  $clm_name) {
			unset($params[$clm_name]);
		}


		if($error_state)res_error(["message"=>"required_error",""=>$required,"status"=>"error"]);

		//Düzenle
		$updated_data['user_id']=$ci->user['id'];
		$updated_data['updated_at']=date("y-m-d h:i:s");
		
		$status = $ci->base_model->update($table_name,$updated_data,$config);
		$response=[];
		if($status){
			$config=(object)[
                "filters"=>$updated_data,
                "sorts"=>["id=false"]
            ];
            $response['record'] =  $ci->base_model->show($table_name,$config);
		}
		$response['status']=$status?"success":"error";

		return $response;
	}
	function db_delete($lang, $table_name,$filter)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/

		
		//Default filtreler
		$where = $ci->auths['default_auths_id'] ?? [];
		$filters=[];
		foreach ($where as $k => $val) {
			$str = strval(explode("=",$val['codes'])[1]);
			$filters[explode("=",$val['codes'])[0]]=eval("return $str;");	
		}
		$ci->lang = $lang;
		$filters2 = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		$config=(object)[
			'filters'=>array_merge($filters,$filters2)
		];
		$data = ($ci->base_model->show($table_name,$config));

		if(empty($data))res_error(["message"=>"data_not_found","status"=>"error"]);
	
		//silme isteği
		$config=(object)[
			'filters'=>["id"=>$data->id]
		];
		
		//Sil
		$status = $ci->base_model->delete($table_name,$config);
		$response=[];
		
		$response['status']=$status?"success":"error";

		return $response;
	}
	function langTranslate($data, $column)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		$lang_support_config=(object)[
			"filters"=>['name' => $column]
		];
		
		$clm_data = $ci->base_model->show('fields', $lang_support_config);
		$lang_support =  empty($clm_data->lang_support) ? FALSE : $clm_data->lang_support == 1 ;
		
		if ($lang_support && !empty($data)) {
			$gecici = (array)json_decode($data);
			return empty($gecici[$ci->lang]) ?
			 $gecici['tr'] : $gecici[$ci->lang];
		} else {
			return $data;
		}
	}
	function get_columns( $table_name)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		$field_list_config=(object)[
			"filters"=>[
				'name' => $table_name
			]
		];
		$field_list = json_decode($ci->base_model->show('lists', $field_list_config)->fields);
		$fields = [];
		foreach ($field_list as $value) {
			$column_data_config=(object)[
				"filters"=>['name' => $value]
			];
			$column_data = $ci->base_model->show('fields', $column_data_config);
			if(!empty($column_data)){
				foreach ($column_data as $k => $v) {

					$fields[$value][$k] = langTranslate($v, $k);
				}

			}
		}
		return $fields;
	}
	
	function get_settings()
    {
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
        $settings=$ci->base_model->list('settings',(object)[]);
        $ci->settings=[];
        foreach ($settings as $value) {
            $ci->settings[$value->set_key]=$value->set_value;
        }
    }
	function get_def_emails()
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
        $def_email=$ci->base_model->list('default_emails',(object)[]);
        $ci->def_email=[];
        foreach ($def_email as $value) {
            $ci->def_email[$value->name]=[
                "title"=>$value->title,
                "content"=>$value->content
            ];
        }
	}
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
        $ci->base_model->set_query($create_table_sql);
        
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
				case 'float':
					$field_type="FLOAT";
					break;
                default:
                    # code...
                    break;
            }

            $before_field = empty($old_field)?"id":$old_field;
            
            $create_field_sql="ALTER TABLE `$table_name` ADD `$field_name` $field_type $field_required AFTER `$before_field`;";   
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

        $auths_list = ["list","create","edit","show","delete"];
	
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
