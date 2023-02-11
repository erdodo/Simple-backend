<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');

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
	function db_list($table_name)
	{
		$ci =& get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		

		//Hangi dil kullanılıyor
		
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
		$body_sorts = $params["sorts"] ?? NULL;//["name=auth"];
		$sorts=[];
		foreach ($body_sorts??[] as $key => $value) {
			$sorts[$key]=$value == true ?"ASC":"DESC";
		}
		
		//Sorgulama(like) bölümü
		$body_like = $params["like"] ?? NULL;//["name=auth"];
		$likes=[];
		foreach ($body_like??[] as $key => $value) {
			$likes[$key]=$value;
		}

		//Filtreleme bölümü
		$body_filters =$params["filters"] ?? NULL;// ["name=lists"];
		$filters=[];
		foreach ($body_filters??[] as $key => $value) {
			$filters[$key]=$value;
		}
		//Default filtreler
		
		foreach (getDBFilters($table_name,'list') as $key => $value) {
			$filters[$key]=$value;
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

		
		$datas = (array)$datas;
		$all_record_count = $ci->base_model->count($table_name,$config);
		$page_count = intval(ceil($all_record_count / ($body_limit ?? 50)));
		
		//Table bilgileri
		$table_info = db_show('lists','name:'.$table_name)['data'];
		
		//Tabloya ait kolonlar
		$fields = get_columns( $table_name);
		//$enums = $ci->getEnums($fields);
		
		if(empty($datas))return (["fields"=> $fields,"message"=>"data_not_found","status"=>"success"]);

		//Yetkisine göre kolon gizleme
		
		foreach (getHideFields($table_name,'list') as  $clm_name) {
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
	function db_show($table_name,$filter)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		$hide_fields=getHideFields($table_name,'show');
		
		
		//Default filtreler
		$filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		foreach (getDBFilters($table_name,'show') as $key => $value) {
			$filters[$key]=$value;
		}
		$config=(object)[
			'filters'=>$filters
		];
		$data = $ci->base_model->show($table_name,$config);
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
	function getDBFilters($table_name,$type)
	{
		$ci =& get_instance();
		$ci->load->model('base_model');
		get_user();
		$user = (array)$ci->input->user;
		$auths_config = (object)[
			"filters"=>[
				"auths_type" => $type,
				"table_name"=>$table_name,
				"auths_group"=>$user['auths_group']
			],
		];
		$auths = (array) $ci->base_model->show('auths',$auths_config);
		if(empty($auths)) res_error(["message"=>"auths_not_found","auths_type" => $type,"table_name"=>$table_name,"status"=>"error"],401);
		/*-------------------------------*/
		
		$where=[];
		if(!empty($auths['default_auths'])){
			foreach (json_decode($auths['default_auths']) as  $value) {
				array_push($where,$ci->base_model->show('default_auths',(object)['filters'=>['id'=>$value]]) ?? NULL);
			}

		}
		
		
		$where = empty($where)?[]:$where;
		$filters=[];
		
		foreach ($where as $k => $val) {
			$val = (array)$val;
			if(empty($val['codes'])) continue;
			if(empty(key((array)eval($val['codes']))) || 
				empty(current((array)eval($val['codes'])))) {
				continue;
			}
			

			$filters[key((array)eval($val['codes']))]=current((array)eval($val['codes']));
			
		}
		return $filters;
	}
	function getHideFields($table_name,$type)
	{
		$ci =& get_instance();
		$ci->load->model('base_model');
		get_user();
		$user = (array)$ci->input->user;
		$auths_config = (object)[
			"filters"=>[
				"auths_type" => $type,
				"table_name"=>$table_name,
				"auths_group"=>$user['auths_group']
			],
		];
		$auths = (array) $ci->base_model->show('auths',$auths_config);
		if(empty($auths)) res_error(["message"=>"auths_not_found","auths_type" => $type,"table_name"=>$table_name,"status"=>"error"],401);

		$hide_fields=(array)json_decode($auths['hide_fields']??'[]')??[];
		return $hide_fields;
	}
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
					$datas[$key]->$clm_name =langTranslate($value[$clm_name],$clm_name);
					/*$lang_record = (array)json_decode($datas[$key]->$clm_name);
					$datas[$key]->$clm_name = empty($lang_record[]) ? $lang_record['tr'] : $lang_record[];*/
				}
				if (!empty($clm['relation_table'])) {
					//NOTE - Eğer kolonun bağlı oldupu bir tablo var ise bu fonksiyon çalışır.
					if (intval($datas[$key]->$clm_name) > 0) {
						// Eğerki kayıtta id tutuluyorsa bu fonksiyon çalışır
						$relation_columns = json_decode($clm['relation_columns']);//Hangi kolonlar isteniyor
						$gecici_id = $datas[$key]->$clm_name;//kayıt değiştirileceği için id bir değişkene atılı
						$datas[$key]->$clm_name = [];//kayıt yeniden yazılmak üzere silinir
						$relation_display = json_decode($clm['relation_display'])??[];
						foreach ($relation_display as $rc_key => $rc_value) {//istenilen bağlı kolonlar döngüye alınır
							$relation_columns_record_config=(object)[
								"filters"=>["id" => $gecici_id]
							];
							$relation_columns_record = 
								(array) $ci->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır
							//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
							if(empty($datas[$key]->$clm_name["show"]))$datas[$key]->$clm_name["show"]="";
							if(!empty($relation_columns_record[$rc_value])){
								$datas[$key]->$clm_name["show"] .= 	langTranslate($relation_columns_record[$rc_value],$rc_value);
							}else{
								$datas[$key]->$clm_name["show"] .= $rc_value;
							}
							
						}

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
								$relation_display = json_decode($clm['relation_display'])??[];
								
								foreach ($relation_display as $rc_key => $rc_value) {//istenilen bağlı kolonlar döngüye alınır
									$relation_columns_record_config=(object)[
										"filters"=>[$clm['relation_id'] => $r_value]
									];
									$relation_columns_record = 
										(array) $ci->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır
									//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
									if(empty($datas[$key]->$clm_name[$r_value]["show"]))$datas[$key]->$clm_name[$r_value]["show"]="";
									if(!empty($relation_columns_record[$rc_value])){
										$datas[$key]->$clm_name[$r_value]["show"] .= 	langTranslate($relation_columns_record[$rc_value],$rc_value);
									}else{
										$datas[$key]->$clm_name[$r_value]["show"] .= $rc_value;
									}
									
								}
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
							$relation_columns_record_config=(object)[
								"filters"=>[$clm['relation_id'] => $val]
							];
							$gecici4 = (array) $ci->base_model->show($clm['relation_table'], $relation_columns_record_config);


							$datas[$key]->$clm_name[$clm['relation_id']] = !empty($gecici4[$clm['relation_id']]) ? $gecici4[$clm['relation_id']] : "";
							$relation_display = json_decode($clm['relation_display'])??[];
								
							foreach ($relation_display as $rc_key => $rc_value) {//istenilen bağlı kolonlar döngüye alınır
								$relation_columns_record_config=(object)[
									"filters"=>[$clm['relation_id'] => $val]
								];
								$relation_columns_record = 
									(array) $ci->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır
								//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
								if(empty($datas[$key]->$clm_name["show"]))$datas[$key]->$clm_name["show"]="";
								if(!empty($relation_columns_record[$rc_value])){
									$datas[$key]->$clm_name["show"] .= 	langTranslate($relation_columns_record[$rc_value],$rc_value);
								}else{
									$datas[$key]->$clm_name["show"] .= $rc_value;
								}
								
							}
							$relation_columns = json_decode($clm['relation_columns']);
							
							foreach ($relation_columns as $rc_key => $rc_value) {
								$relation_columns_record_config=(object)[
									"filters"=>[$clm['relation_id'] => $val]
								];
								$gecici3 = (array) $ci->base_model->show($clm['relation_table'], $relation_columns_record_config);
								
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
				if ($clm['type'] == 'array' || $clm['type'] == 'json' ) {
					$array_record = empty($datas[$key]->$clm_name)? "[]":$datas[$key]->$clm_name;
					if(gettype($array_record) =='array' ){}
					 else $datas[$key]->$clm_name = json_decode($array_record) ?? $datas[$key]->$clm_name;
				}
				if ($clm['type'] == 'file' || $clm['type'] == 'image' ) {
					$ci->load->helper('url');
					if(!empty($datas[$key]->$clm_name)){
						$files = json_decode($datas[$key]->$clm_name);
						
						$datas[$key]->$clm_name=[];
						if(!empty($files)){

							foreach ($files as $file_key => $yakala) {
								
								$datas[$key]->$clm_name[$file_key]=[];
								$datas[$key]->$clm_name[$file_key]['full_link'] = empty($yakala->full)?'':   base_url().'public/uploads/'.$yakala->full  ;
								$datas[$key]->$clm_name[$file_key]['mini_link'] = empty($yakala->mini)?'':   base_url().'public/uploads/'.$yakala->mini  ;
								$datas[$key]->$clm_name[$file_key]['full'] = empty($yakala->full)?'':   $yakala->full  ;
								$datas[$key]->$clm_name[$file_key]['mini'] = empty($yakala->mini)?'':   $yakala->mini  ;
							}
						}
						
					}
					

				}

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
					$data->$clm_name =langTranslate($data->$clm_name,$clm_name);
					/*$lang_record = (array)json_decode($data->$clm_name);
					$data->$clm_name = empty($lang_record[$ci->lang]) ? $lang_record['tr'] : $lang_record[$ci->lang];
					*/

				}
				
				if (!empty($clm['relation_table'])) {
					
					//NOTE - Eğer kolonun bağlı oldupu bir tablo var ise bu fonksiyon çalışır.
					
					if (intval($data->$clm_name) > 0) {
						$gecici_id = $data->$clm_name;//kayıt değiştirileceği için id bir değişkene atılı
						$data->$clm_name = [];//kayıt yeniden yazılmak üzere silinir
						$relation_display = json_decode($clm['relation_display'])??[];
								
							foreach ($relation_display as $rc_key => $rc_value) {//istenilen bağlı kolonlar döngüye alınır
								$relation_columns_record_config=(object)[
									"filters"=>["id" => $gecici_id]
								];
								$relation_columns_record = 
								(array) $ci->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır
								//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
								if(empty($data->$clm_name["show"]))$data->$clm_name["show"]="";
								if(!empty($relation_columns_record[$rc_value])){
									$data->$clm_name["show"] .= 	langTranslate($relation_columns_record[$rc_value],$rc_value);
								}else{
									$data->$clm_name["show"] .= $rc_value;
								}
								
							}
						// Eğerki kayıtta id tutuluyorsa bu fonksiyon çalışır
						$relation_columns = json_decode($clm['relation_columns']);//Hangi kolonlar isteniyor
						
						
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
								$relation_display = json_decode($clm['relation_display'])??[];
								
							foreach ($relation_display as $rc_key => $rc_value) {//istenilen bağlı kolonlar döngüye alınır
								$relation_columns_record_config=(object)[
									"filters"=>[$clm['relation_id'] => $r_value]
								];
								$relation_columns_record = 
								(array) $ci->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır
								//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
								if(empty($data->$clm_name[$r_value]["show"]))$data->$clm_name[$r_value]["show"]="";
								if(!empty($relation_columns_record[$rc_value])){
									$data->$clm_name[$r_value]["show"] .= 	langTranslate($relation_columns_record[$rc_value],$rc_value);
								}else{
									$data->$clm_name[$r_value]["show"] .= $rc_value;
								}
								
							}
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
							$relation_columns_record_config=(object)[
								"filters"=>[$clm['relation_id'] => $val]
							];
							$gecici4 = (array) $ci->base_model->show($clm['relation_table'], $relation_columns_record_config);


							$data->$clm_name[$clm['relation_id']] = !empty($gecici4[$clm['relation_id']]) ? $gecici4[$clm['relation_id']] : "";
							$relation_display = json_decode($clm['relation_display'])??[];
								
							foreach ($relation_display as $rc_key => $rc_value) {//istenilen bağlı kolonlar döngüye alınır
								$relation_columns_record_config=(object)[
									"filters"=>[$clm['relation_id'] => $val]
								];
								$relation_columns_record = 
								(array) $ci->base_model->show($clm['relation_table'],$relation_columns_record_config );//geçiçi olarak kaydettiğimiz id ile gerçek veriye ulaşılır
								//kayıt bir objeye dönüştürülerek id ve diğer kolonlar yazılır
								if(empty($data->$clm_name["show"]))$data->$clm_name["show"]="";
								if(!empty($relation_columns_record[$rc_value])){
									$data->$clm_name["show"] .= 	langTranslate($relation_columns_record[$rc_value],$rc_value);
								}else{
									$data->$clm_name["show"] .= $rc_value;
								}
								
							}
							$relation_columns = json_decode($clm['relation_columns']);
							
							foreach ($relation_columns as $rc_key => $rc_value) {
								$relation_columns_record_config=(object)[
									"filters"=>[$clm['relation_id'] => $val]
								];
								$gecici3 = (array) $ci->base_model->show($clm['relation_table'], $relation_columns_record_config);
								
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
				if ($clm['type'] == 'array' || $clm['type'] == 'json' ) {
					$array_record = empty($data->$clm_name)? "[]":$data->$clm_name;
					if(gettype($array_record) =='array' ){}
					 else $data->$clm_name = json_decode($array_record) ?? $data->$clm_name;
				
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
	function db_create($table_name)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/

		
		
		$fields= get_columns($table_name);
		//Yetkisine göre kolon gizleme
		$hide_fields=getHideFields($table_name,'create');
		array_push($hide_fields,'id','own_id','user_id','created_at','updated_at','companies_id');
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
	function db_add($table_name)
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
		$params = (array)$params;
		
		//Hata basmalar
		$response=[
			"error"=>[
				"required"=>[],
				"unique"=>[]
			],
			"status"=>"success"
		];
		
		//Benzersiz kontrolleri
		$columns = get_columns($table_name);
		
		//Zorunluluk kontrolleri
		foreach ($columns as $key => $value) {
			if($value['required'] == 1){
				if(empty($params[$key])){
					array_push($response['error']['required'],$key);
					$response['status']="error";
					$response['message']="required_error";
				}
			}
			if($value['benzersiz'] == 1 && !empty($params[$key])){
				if(!empty(ad_show($table_name,$key.':'.$params[$key]))){
					array_push($response['error']['unique'],$key);
					$error_state=TRUE;
					$response['status']="error";
					$response['message']="unique_error";
				}
			}
			if($value['type']=='file'){
				$params[$key]=json_encode(upload_file($key));
			}
			if($value['type']=="password" && !empty($params[$key])){
				$params[$key]=password_hash($params[$key], PASSWORD_DEFAULT);
			}
		}
		
		
		
		
		//Gizlenecek kolonları veritabanına gönderme
		$hide_fields=getHideFields($table_name,'create');
		array_push($hide_fields,'id','own_id','user_id','created_at','updated_at');
		
		foreach ($hide_fields as  $clm_name) {
			unset($params[$clm_name]);
			
		}
		if($response['status']=="error")res_error($response);
		
		if($table_name == 'lists')$params= create_table($params);
		
		//Ekle
		

		$params['companies_id']=$ci->user['companies_id'];
		
		$params['own_id']=$ci->user['id'];
		$params['user_id']=$ci->user['id'];
		$params['created_at']=date("Y-m-d H:i:s");
		$params['updated_at']=date("Y-m-d H:i:s");

		//Olmayan kolon gelirse sil
		foreach ($params as $key => $value) {
			if(empty($columns[$key])){
				unset($params[$key]);
			}
		}
		
		$status = $ci->base_model->add($table_name,$params);
		$response=[];

		if($status){
			$config=(object)[
                "filters"=>$params,
                "sorts"=>["id=false"]
            ];
            $response['record'] =  $ci->base_model->show($table_name,$config);
			$response['status']="success";
		}else{
			$response['status']="error";
			$response['message']=$ci->db->error()['message'];
		}
		

		return $response;
		
	}
	function db_edit($table_name,$filter)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		$filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		


		//Default filtreler
		foreach (getDBFilters($table_name,'edit') as $key => $value) {
			$filters[$key]=$value;
		}
		
		$config=(object)[
			'filters'=>$filters
		];
		
		$data = ($ci->base_model->show($table_name,$config));
		if(empty($data))res_error(["message"=>"data_not_found","status"=>"error"]);

		$fields= get_columns( $table_name);
		//Yetkisine göre kolon gizleme
		$hide_fields=getHideFields($table_name,'edit');
		array_push($hide_fields,'id','own_id','user_id','created_at','updated_at',"companies_id");
		foreach ($hide_fields as  $clm_name) {
			unset($fields[$clm_name]);
		}
		//field__show($fields,$data);
		$response=[
			"data"=>$data,
			"fields"=>$fields,
			'status'=>$data?"success":"error"
		];

		return $response;
	}
	function db_update($table_name,$filter)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/
		//düzenleme isteği
		$filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		//Default filtreler
		foreach (getDBFilters($table_name,'edit') as $key => $value) {
			$filters[$key]=$value;
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
		

		//Hata basmalar
		$response=[
			"error"=>[
			"required"=>[],
			"unique"=>[]
			],
			"status"=>"success"
		];
		//Benzersiz kontrolleri
		$columns = get_columns($table_name);

		//Ön güncelleme
		$updated_data=[];
		foreach ($filtered_data as $key => $value) {
			$updated_data[$key]=empty($params[$key])?$value:$params[$key];
		}
		
		
		//Zorunluluk kontrolleri
		foreach ($columns as $key => $value) {
			if($value['required'] == 1){
				if(empty($updated_data[$key])){
					array_push($response['error']['required'],$key);
					$response['status']="error";
				}
			}
			if($value['benzersiz'] == 1 && !empty($params[$key])){
				if(!empty(ad_show($table_name,$key.':'.$params[$key]))){
					array_push($response['error']['unique'],$key);
					$error_state=TRUE;
					$response['status']="error";
				}
			}
			if($value['type']=='file'){
				
				$f = (array)json_decode($params['old_'.$key]??"[]");
				//dd(json_decode($params['old_'.$key]));
				$g=array_merge($f, (array)upload_file($key));
				$updated_data[$key]=json_encode($g);
				
			}
		}


		//Olmayan kolon gelirse sil
		foreach ($params as $key => $value) {
			if(empty($columns[$key])){
				unset($params[$key]);
			}
			
		}
		//Gizlenecek kolonları veritabanına gönderme
		$hide_fields=getHideFields($table_name,'edit');
		array_push($hide_fields,'id','own_id','user_id','created_at','updated_at');
		foreach ($hide_fields as  $clm_name) {
			unset($params[$clm_name]);
		}


		if($response['status']=="error")res_error($response);
		if($table_name == 'lists')$updated_data = edit_table($updated_data,$filtered_data);
		
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
	function db_delete($table_name,$filter)
	{
		$ci = get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/

		
		//Default filtreler
		$where = $ci->auths['default_auths'] ?? [];
		$filters=[];
		
		
		$filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		foreach (getDBFilters($table_name,'delete') as $key => $value) {
			$filters[$key]=$value;
		}
		
		$config=(object)[
			'filters'=>$filters
		];
		$data = ($ci->base_model->show($table_name,$config));
		
		if(empty($data))res_error(["message"=>"data_not_found","status"=>"error"]);
		if($table_name == 'lists')delete_table($data);
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
		$lang_support_support_config=(object)[
			"filters"=>['name' => $column]
		];
		
		$clm_data = $ci->base_model->show('fields', $lang_support_support_config);
		$lang_support =  empty($clm_data->lang_support) ? FALSE : $clm_data->lang_support == 1 ;
		
		if ($lang_support && !empty($data) ) {
			
			$gecici = (array)json_decode($data);
			
			if(empty($gecici)){
				return $data;
			}
		
			return empty($gecici[$ci->user['language_id']]) ?
			 $gecici['tr'] : $gecici[$ci->user['language_id']];
		} else {
			return $data;
		}
	}
	function get_columns($table_name)
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
	
	function db_enums($table_name,$clm_name){
		$ci =& get_instance();
		$ci->load->model('base_model');
		$ci->user = (array)$ci->input->user;
		$ci->auths = (array)$ci->input->auths;
		/*-------------------------------------*/

		$table_info = ad_show('lists',"name:".$table_name);
		
		if(array_search($clm_name,(array)json_decode($table_info->fields)) == FALSE){
			$response['message']="field_not_found";
			$response['status']="error";
			res_error($response);
			die();
		}

		$field = ad_show('fields',"name:".$clm_name);
		$table_data=[];
		if(!empty($field->relation_table)){
			$filters=[];
			foreach (getDBFilters($table_name,'enums') as $key => $value) {
				$filters[$key]=$value;
			}        
			$config=(object)[
				"filters"=>$filters,
				"likes"=>[],
				"sorts"=>[],
				"limit"=>1000,
				"page"=>1,
			];
			
			$ad_table_data = $ci->base_model->list($field->relation_table,$config);
			
			foreach ($ad_table_data as $key => $value) {
				$val = (array)$value;
				$new_val=[];
				$relation_display = json_decode($field->relation_display)??[];
								
				foreach ($relation_display as $v) {//istenilen bağlı kolonlar döngüye alınır
					
					
					if(empty($new_val["show"]))$new_val["show"]="";
					
					if(!empty($val[$v])){
						$new_val["show"] .= langTranslate($val[$v],$v);
					}else{
						$new_val["show"] .= $v;
					}
					
				}
				$relation_columns = json_decode($field->relation_columns);
				foreach ($relation_columns as  $v) {
					$new_val[$v]=langTranslate($val[$v],$v);
				}
				$new_val[$field->relation_id] = $val[$field->relation_id];
				
				$table_data[$val[$field->relation_id]]=$new_val;
			}
			$response['records']=$table_data;
			$response['type']="table";
			$response['status']="success";
		}
		else if(!empty($field->enums)){
			$table_data = json_decode($field->enums);
			$response['records']=$table_data;
			$response['type']="enums";
			$response['status']="success";
		}else{
			$response['message']="data_not_found";
			$response['status']="error";
		}
		
		
		
		return $response;
	}
