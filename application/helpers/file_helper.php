<?php
defined('BASEPATH') or exit('No direct script access allowed');

	function upload_file($clm_name)
	{
		$CI = get_instance();
        if ($CI->input->method()) {
			
			if ($_FILES) {
				$config['upload_path'] = './public/uploads/';
				$config['allowed_types'] = '*';
				$config['max_size'] = '0';
				$config['max_filename'] = '255';
				$config['encrypt_name'] = FALSE;

				$CI->load->library('upload', $config);
				$CI->load->library('image_lib');
                $files = $_FILES;
				$cpt = count($_FILES [$clm_name]['name']);
				$file=[];
				for ($i = 0; $i < $cpt; $i ++) {
					
						
					if (file_exists($config['upload_path'] . $files[$clm_name]['name'][$i])) {
						
						echo ('File already exists => ' . $config['upload_path'] . $files[$clm_name]['name'][$i]);
						return;
					} else {
						if (!file_exists($config['upload_path'])) {
							mkdir($config['upload_path'], 0777, true);
						}

						$name = time().$files[$clm_name]['name'][$i];
						$_FILES[$clm_name]['name'] = $name;
						$_FILES[$clm_name]['type'] = $files[$clm_name]['type'][$i];
						$_FILES[$clm_name]['tmp_name'] = $files[$clm_name]['tmp_name'][$i];
						$_FILES[$clm_name]['error'] = $files[$clm_name]['error'][$i];
						$_FILES[$clm_name]['size'] = $files[$clm_name]['size'][$i];

						
						
						$CI->upload->do_upload($clm_name);
						$image_uploaded = $CI->upload->data();

						$file[$i]['full'] = pathinfo($image_uploaded['full_path'])['basename'];
						$file[$i]['is_image'] = $image_uploaded['is_image'];
						$params=[
							"file_name"=> $_FILES[$clm_name]['name'],
							"file_type"=>$_FILES[$clm_name]['type'],
							"file_size"=>$_FILES[$clm_name]['size'],
							"is_image"=>$file[$i]['is_image']
						];
						ad_add('files',$params);
						$CI->upload->do_upload($clm_name);
						$image_data =   $CI->upload->data();
						if($image_uploaded['is_image']){
							$configer =  array(
								'image_library'   => 'gd2',
								'source_image'    =>  $image_data['full_path'],
								'maintain_ratio'  =>  TRUE,
								'width'           =>  250,
								'height'          =>  250,
								"overwrite"		  =>  FALSE
								);
								$CI->image_lib->clear();
								$CI->image_lib->initialize($configer);
								$CI->image_lib->resize();
								$file[$i]['mini'] = pathinfo($image_data['full_path'])['basename'];
						}
						
						
					}
				}
				return $file;
			
			} else {

				return false;
			}
		}
    }
	