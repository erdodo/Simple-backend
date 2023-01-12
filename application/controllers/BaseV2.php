<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');
class BaseV2 extends CI_Controller
{
	public $lang = 'tr';
	public $user = [];	
	public $auths = [];	
    public $auths_group=[];
    public $companies=[];
	
	public function __construct()
    {
        parent::__construct();
		$this->load->model('base_model');
        get_user();
		$this->user = (array)$this->input->user;
		$this->auths = (array)$this->input->auths;
        $this->auths_group = (array)$this->input->auths_group;
        $this->companies = (array)$this->input->companies;
    }
	public function file_upload()
	{
		$data =  upload_file('file');
		if($data)res_success(["data"=>$data,"status"=>"success"]);
		else res_error(["message"=>"error","status"=>"error"]);
	}
    public function file_details()
    {
        get_settings();
        

        $for_comp= $this->settings['file_limit_for_company'];
        $quota=0;
        $file_list=[];
        if($for_comp){
            $this->companies['file_quota'];
            $config=(object)[
                "filters"=>[
                    "companies"=>$this->companies['id']
                ],
                "limit"=>10000,
                "page"=>1,
            ];
            $file_list=$this->base_model->list('files',$config);
        }
        else{
            $quota= $this->auths_group['file_quota'];
            $config=(object)[
                "filters"=>[
                    "own_id"=>$this->user['id']
                ],
                "limit"=>10000,
                "page"=>1,
            ];
            $file_list=$this->base_model->list('files',$config);
        }
        $total_file_size=0;
        foreach ($file_list as $value) {
            $total_file_size += $value->file_size;
        }

        $response=[];
        $response['quota']=$quota;
        $response['quota_display']=$this->formatBytes($quota);
        $response['total_file_size']=$total_file_size;
        $response['remaining_quota']=$quota - $total_file_size;
        $response['remaining_quota_display']=$this->formatBytes($quota - $total_file_size);
        $response['file_list']=$file_list;
        dd($response);
        
    }
    private function formatBytes($size, $precision = 2) { 
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');   
    
        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    
    } 
}
