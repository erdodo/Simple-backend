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
        get_settings();
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
    public function file_delete($filter)
    {
        $filters = (intval($filter) > 0)?["id"=>$filter]:[explode(":",$filter)[0]=>explode(":",$filter)[1]];
		foreach (getDBFilters() as $key => $value) {
			$filters[$key]=$value;
		}
		$config=(object)[
			'filters'=>$filters
		];
		$data = ($this->base_model->show("files",$config));
      
        ad_delete('files',$filter);
        unlink(getcwd()."/public/uploads/".$data->file_name);
        if($data->is_image){
            unlink(getcwd()."/public/uploads/".$data->mini_name);
        }
        dd($data);

    }
    private function formatBytes($size, $precision = 2) { 
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');   
    
        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    
    } 
    public function front_cache()
    {
        $lists=['language'];
        $shows=[
            "auths_group"=>$this->user['auths_group']
        ];
        $response=[];
        $response['time']=floor(microtime(true) * 1000)+intval($this->settings['front_cache_time']);
        /*----------------------------------------- */
        $auths=$this->base_model->set_query("SELECT `table_name`,`auths_type` FROM `auths` WHERE `auths_group` = ".$this->user['auths_group'])->result();
        $response['auths']=[];
        foreach ($auths as  $value) {
            if(empty($response['auths'][$value->table_name]))$response['auths'][$value->table_name]=[];
            array_push($response['auths'][$value->table_name],$value->auths_type);
        }
        /*----------------------------------------- */
        $response['profile']=db_show('users','id:'.$this->user['id'])['data'];
        /*----------------------------------------- */
        $front_langs=$this->base_model->set_query("SELECT `name`,`display` FROM `front_langs`")->result();
        $response['front_langs']=[];
        foreach ($front_langs as $value) {
            $response['front_langs'][$value->name]=langTranslate($value->display,'display');
            
        }
        /*----------------------------------------- */
        $language=$this->base_model->set_query("SELECT `name`,`display` FROM `language`")->result();
        $response['language']=[];
        foreach ($language as $value) {
            $response['language'][$value->name]=langTranslate($value->display,'display');
            
        }
        /*----------------------------------------- */
        $table_group=$this->base_model->set_query("SELECT `name`,`icon`,`display`,`table_group_tables` FROM `table_group`")->result();
        $response['table_group']=[];
        foreach ($table_group as $value) {
            $table_group_tables= json_decode($value->table_group_tables);
            $new_table_group_tables=[];
            foreach ($table_group_tables as $value2) {
                $table_display_query = "SELECT `display` FROM `lists` WHERE `name` = '$value2'";
                $table_display = $this->base_model->set_query($table_display_query);
                
                $new_table_group_tables[$value2]= $table_display ? langTranslate($table_display->row()->display,'display') : NULL;
            }
            $response['table_group'][$value->name]=[
                "display" => langTranslate($value->display,'display'),
                "icon"=> $value->icon,
                "table_group_tables"=> $new_table_group_tables
            ];

        }
        //$response['table_group']=db_list('table_group')['records'] ?? NULL;
        res_success($response);
    }
    public function send_notification()
    {
        $this->load->model('base_model');
        //SELECT * FROM `notification` WHERE `notif_time` < '2023-02-01 00:23:19'
        //SELECT * FROM `notification` WHERE `notif_time` < '2023-02-01 00:28:56' AND `state` = 1
        
        
        $res = $this->base_model->set_query("SELECT * FROM `notification` WHERE `notif_time` < '".date("Y-m-d H:i:s")."' AND `state` = 1");
    
        if($res->row() == NULL)return;
        $url2= 'https://push.techulus.com/api/v1/notify/2e4b1d86-ef9a-46dc-bbe9-1f0b85ecd46f?title='.strval($res->row()->notification_title).'&body='.strval($res->row()->notification_content);
        $url = 'https://api.telegram.org/bot6135972814:AAFYOwdxRSMuL5Nl-XRrb6EnhQLlNk7bHFM/sendMessage?chat_id=@erdo_simple&text='.strval($res->row()->notification_content); 
    
        $url=str_replace(' ','%20',$url);
        if($res){

    
            $curl = curl_init($url);
            $data = [];
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(''));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            curl_close($curl);
            //print_r($result);
            if(json_decode($result)->ok){
                $up=ad_update('notification','id:'.$res->row()->id , ['state'=>0]);
                
            }
        }
    
    }
}
