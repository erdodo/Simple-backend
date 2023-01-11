<?php
defined('BASEPATH') or exit('No direct script access allowed');

     function upload_file($clm_name)
    {
        $CI =& get_instance();
        if ($CI->input->method()) {
			if (empty($_FILES[$clm_name]['name']) != 1) {
				$config['upload_path'] = './public/uploads/';
				$config['allowed_types'] = '*';
				$config['max_size'] = '0';
				$config['max_filename'] = '255';
				$config['encrypt_name'] = TRUE;

				$CI->load->library('upload', $config);
				$CI->load->library('image_lib');
                
				if (file_exists($config['upload_path'] . $_FILES[$clm_name]['name'])) {
					
					echo ('File already exists => ' . $config['upload_path'] . $_FILES[$clm_name]['name']);
					return;
				} else {
					if (!file_exists($config['upload_path'])) {
						mkdir($config['upload_path'], 0777, true);
					}
					$CI->upload->do_upload($clm_name);
					$image_uploaded = $CI->upload->data();

					$file['full'] = pathinfo($image_uploaded['full_path'])['basename'];
					$file['is_image'] = $image_uploaded['is_image'];
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
							$file['mini'] = pathinfo($image_data['full_path'])['basename'];
					}
					
					return $file;
				}
			} else {

				return false;
			}
		}
    }