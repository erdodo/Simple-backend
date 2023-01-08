<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');
class Account extends CI_Controller
{
    public $settings=[];
    public function __construct()
    {
        parent::__construct();
        $this->load->model('base_model');
        $set_data=$this->base_model->list('settings',(object)[]);
        $this->settings=[];
        foreach ($set_data as $value) {
            $this->settings[$value->set_key]=$value->set_value;
        }
        $def_email=$this->base_model->list('default_emails',(object)[]);
        $this->def_email=[];
        foreach ($def_email as $value) {
            $this->def_email[$value->name]=[
                "title"=>$value->title,
                "content"=>$value->content
            ];
        }
    }
    public function login()
    {
        if($this->input->method() == 'get'){
            $this->output
			->set_content_type('application/json', 'utf-8')
			->set_status_header(405)
			->_display();
            die();

        }
        // POST, FORM-DATA, BODY gibi isteklerin tamamını destekler
		$body = (array)json_decode($this->input->raw_input_stream) ?? [];
		$post = $this->input->post() ?? [];
		$params=[];
		if(!empty($post))$params = $post;
		if(!empty($body))$params = $body;

        $this->load->model('base_model');

        $token_hours_config=(object)['filters'=>['set_key'=>"token_hours"]];
		$token_hours = ($this->base_model->show('settings',$token_hours_config))->set_value;

        $multi_login_config=(object)['filters'=>['set_key'=>"multi_login"]];
		$multi_login = ($this->base_model->show('settings',$multi_login_config))->set_value;

        $step_login_config=(object)['filters'=>['set_key'=>"step_login"]];
		$step_login = ($this->base_model->show('settings',$step_login_config))->set_value;

        $first_step_login_fields_config=(object)['filters'=>['set_key'=>"first_step_login_fields"]];
		$first_step_login_fields = json_decode($this->base_model->show('settings',$first_step_login_fields_config)->set_value??"[]") ?? [];

        

        $response=[];
        if($step_login == 1 && !empty($params['email']) && empty($params['password'])){
            //echo 'Adımlı giriş 1. adım';
            $users_config=(object)['filters'=>['email'=>$params['email']]];
            $data = (array)$this->base_model->show('users',$users_config);
            if(!empty($data)){
                foreach ($first_step_login_fields as $clm) {
                    $response['data'][$clm]=$data[$clm];
                }

            }
            else $response['data']="user_not_found";
            $response['status']=$data?"success":"error";
        }
        else if($step_login == 0 && !empty($params['email']) && empty($params['password'])){
            //echo 'Şifre boş';
            $response['data']['required']=["password"];
            $response['status']="error";
        }else if( empty($params['email']) && empty($params['password'])){
            //echo "Eposta ve şifre boş";
            $response['data']['required']=["password","email"];
            $response['status']="error";
        }else if($step_login == 0 && empty($params['email']) && !empty($params['password'])){
            //echo "Eposta boş";
            $response['data']['required']=["email"];
            $response['status']="error";
        }else if($step_login == 1 && empty($params['email']) && !empty($params['password'])){
            //echo "Adımlı giriş ama eposta boş";
            $response['data']['required']=["email"];
            $response['status']="error";
        }else if(!empty($params['email']) && !empty($params['password'])){
            //echo "Direkt giriş";
            $users_config=(object)['filters'=>['email'=>$params['email'],'password'=>$params['password']]];
            $data = (array)$this->base_model->show('users',$users_config);
            if(!empty($data)){
                foreach ($first_step_login_fields as $clm) {
                    $response['data'][$clm]=$data[$clm];
                }
            }
            else $response['data']="user_not_found";
            $response['status']=$data?"success":"error";
        }
        if($response['status']=='success'){
            $users_config=(object)['filters'=>['email'=>$params['email'],'password'=>$params['password']]];
            $data = (array)$this->base_model->show('users',$users_config);
            $tokens = json_decode($data['token']??"[]")??[];
            $token=md5(microtime(TRUE));
            //şehir bilgisi
            $ip = $_SERVER['REMOTE_ADDR'];
            $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
            
            $params=[
                'token'=>$token,
                "city"=>$details->city??NULL,
                "user_ip"=>$_SERVER['REMOTE_ADDR'],
                "login_time"=>date('Y-m-d H:i:s'),
                "last_time"=>date("Y-m-d H:i:s", strtotime("+".$token_hours." Hours")),
                "device"=>$_SERVER['HTTP_USER_AGENT']
            ];
            array_push($tokens,$params);
            $data['token']=$tokens;
            $status = $this->base_model->update('users',["token"=>json_encode($tokens)],$users_config);
            $response['data']=$status ? ['token'=>$token]:'not_login';
            $response['status']=$response['data']?"success":"error";

        }
        
        $this->output
			->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response))
			->set_status_header($response['status'] == 'success'?200:400)
			->_display();
            die();
    }
    public function register()
    {
        
        // POST, FORM-DATA, BODY gibi isteklerin tamamını destekler
		$body = (array)json_decode($this->input->raw_input_stream) ?? [];
		$post = $this->input->post() ?? [];
        $get = $this->input->get() ?? [];
		$params=[];
		if(!empty($post))$params = $post;
		if(!empty($body))$params = $body;
        if(!empty($get))$params = $get;

        //TODO boş kontrolü

        $generator = "1357902468";
        $otp_code = "";
        for ($i = 1; $i <= 6; $i++) {
            $otp_code .= substr($generator, (rand()%(strlen($generator))), 1);
        }
        $params['token'] = $otp_code;
        
        $this->load->model('base_model');
        $add_state =  $this->base_model->add('users',$params);
        if($add_state){
            $this->load->library('../controllers/Email');

            $email_content = "";
            foreach (json_decode($this->def_email['new_user_otp']['content']) as $value) {
                $value= str_replace('%otp_code%',$otp_code,$value);
                //STUB - Burada yapılan replace işlemini globalleştir
                $email_content .= "$value <br/><br/><br/>";
            }
            $email_title = "";
            foreach (json_decode($this->def_email['new_user_otp']['title']) as $value) {
                $email_title .= "$value  - ";
            }
        
        
            $send = $this->email->send_email("erdoganyesil3@gmail.com",$email_title,$email_content);
        }
        

        dd($add_state);
        /*$this->output
			->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response))
			->set_status_header($response['status'] == 'success'?200:400)
			->_display();
            die();*/
    }
    public function create_pass()
    {
        $this->load->model('base_model');
        // POST, FORM-DATA, BODY gibi isteklerin tamamını destekler
		$body = (array)json_decode($this->input->raw_input_stream) ?? [];
		$post = $this->input->post() ?? [];
        $get = $this->input->get() ?? [];
		$params=[];
		if(!empty($post))$params = $post;
		if(!empty($body))$params = $body;
        if(!empty($get))$params = $get;

        echo $params['token'];
    }
}