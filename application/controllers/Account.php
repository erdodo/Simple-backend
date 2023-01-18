<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Account extends CI_Controller
{
    public $settings=[];
    public $def_email=[];
    public function __construct()
    {
        parent::__construct();
        session_start();
        $this->load->model('base_model');
        get_settings();
        get_def_emails();
        $this->input->user=ad_show('users','id:1');
    }
    public function index()
    {
        header('Content-Type: text/html; charset=UTF-8');
        $this->load->view('document');
    }
    public function login()

    {
        header('Content-Type: application/json');
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

		$token_hours = $this->settings['token_hours'];
		$multi_login = $this->settings['multi_login'];
          $step_login = $this->settings['step_login'];
		$first_step_login_fields = json_decode($this->settings['first_step_login_fields']);

        $data=[];

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
        }else if($step_login == 0 && !empty($params['email']) && empty($params['password'])){
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
            $users_config=(object)['filters'=>['email'=>$params['email']]];
            $data = (array)$this->base_model->show('users',$users_config);
            $verify = password_verify($params['password'], $data['password']);
            if($verify){
                foreach ($first_step_login_fields as $clm) {
                    $response['data'][$clm]=$data[$clm];
                }
            }
            else $response['data']="user_not_found";
            $response['status']=$data?"success":"error";
        }

        if($response['status']=='success'){
            
            $tokens = json_decode($data['token']??"[]")??[];
            $token=md5(microtime(TRUE));
            //şehir bilgisi
            $ip = $_SERVER['REMOTE_ADDR'];
            $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
            
            $params=[
                'token'=>$token,
                "city"=>$details->city??NULL,
                "user_ip"=>$ip,
                "login_time"=>date('Y-m-d H:i:s'),
                "last_time"=>date("Y-m-d H:i:s", strtotime("+".$token_hours." Hours")),
                "device"=>$_SERVER['HTTP_USER_AGENT']
            ];
            $title="";
            $content="";
            foreach (json_decode($this->def_email['new_ip_login']['title']) as $value) {
                $title .= "$value - ";
            }
            foreach (json_decode($this->def_email['new_ip_login']['content']) as $value) {
                $content .= $value." <br/><br/><hr/><br/><br/> ";
            }
            send_email($data['email'],$title,$content);
            foreach ($tokens as $value) {
                if($value->user_ip != $ip){
                    echo "farklı";
                }
            }
            array_push($tokens,$params);
            $data['token']=$tokens;
            
            $status = $this->base_model->update('users',["token"=>json_encode($tokens)],$users_config);
            $response['data']=$status ? ['token'=>$token]:'not_login';
            $response['status']=$response['data']?"success":"error";

        }
        $response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
        
    }
    public function register()
    {
        if($this->input->method()=='get'){
            $columns =  json_decode($this->settings['register_columns']);
            $fields=[];
            foreach ($columns as $value) {
                $fields[$value] = db_show('fields',"name:$value")['data'];
            }

            $fields['captcha'] =captcha();
            echo json_encode($fields);
            die();
        }else{
             // POST, FORM-DATA, BODY gibi isteklerin tamamını destekler
            $body = (array)json_decode($this->input->raw_input_stream) ?? [];
            $post = $this->input->post() ?? [];
            $params=[];
            if(!empty($post))$params = $post;
            if(!empty($body))$params = $body;
            
            if(empty($params["captcha"]) || md5($params["captcha"]) != $_SESSION['captcha_control']){
                res_error(['message'=>"captcha_error","status"=>"error"]);
            }
            
            
            $columns =  json_decode($this->settings['register_columns']);
            $response=[
                "message"=>"",
                "error"=>["required"=>[]],
                "status"=>"success"
            ];
            foreach ($columns as  $value) {
                if(empty($params[$value])){
                    array_push($response['error']['required'],$value);
                    $response['status']="error";
                    $response['message']="required_error";
                }
                
                if(ad_show('fields',"name:".$value)->type=="password"){
                    $p =password_hash($params[$value], PASSWORD_DEFAULT);
                    $params[$value]=$p;
                }
                print_r(ad_show('fields',"name:".$value)->type);
            }
            if($response['status']=="error"){
                res_error($response);
               
            }
            if(!empty(ad_show('users','email:'.$params['email']))){
                $response["error"]['unique']=["email"];
                $response['status']="error";
                $response['message']="unique_error";
                res_error($response);
            }
            unset($params['captcha']);
            unset($response['error']);
            
            
            $this->load->model('base_model');
            $response['status'] =  $this->base_model->add('users',$params)?'success':'error';
            $response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
           
        }
       
    }
    public function token_control()
    {
        $token = $this->input->request_headers()['Authorization'] ?? NULL;
        if( empty($token)  || strlen($token) != 32){
            res_error(["message"=>"token_error","status"=>"error"],401);
        }
        
        $this->input->user = ($this->base_model->query("SELECT * FROM `users` WHERE `token` LIKE '%$token%'"));
        if(empty($this->input->user)){
            res_error(["message"=>"user_not_found","status"=>"error"],401);
        }
        echo json_encode(['status'=>"success"]);
        
    }
    public function forgot_password()
    {
        //Kullanıcıdan email istenecek ve o emaile otp gönderilecek
        //captcha koyulabilir
        
        if($this->input->method()=='get'){
            
            $fields['email'] = ad_show('fields',"name:email");
            $fields['captcha'] =captcha();
            echo json_encode($fields);
            die();
        }else{
            $body = (array)json_decode($this->input->raw_input_stream) ?? [];
            $post = $this->input->post() ?? [];
            $params=[];
            if(!empty($post))$params = $post;
            if(!empty($body))$params = $body;

            if(empty($params["captcha"]) || md5($params["captcha"]) != $_SESSION['captcha_control']){
                res_error(["message"=>"captcha_error","status"=>"error"]);
               
            }
            if(empty(ad_show('users','email:'.$params['email']))){
                res_error(["message"=>"user_not_found","status"=>"error"]);
                           }
            
            $email = $this->def_email['forgot_password'];
            $otp="";
            $generator = "1357902468";
            for ($i = 1; $i <= 6; $i++) {
                $otp .= substr($generator, (rand()%(strlen($generator))), 1);
            }
            $_SESSION['forgot_password']=$otp;
            $title="";
            $content="";
            foreach (json_decode($email['title']) as $value) {
                $title .= "$value - ";
            }
            foreach (json_decode($email['content']) as $value) {
                $content .= str_replace("%otp_code%",$otp,$value)." <br/><br/><hr/><br/><br/> ";
            }
            $status= send_email($params['email'],$title,$content);
            $response['status']=$status?'success':$status;
            
            if($this->settings['dev_mode']){
                $response['otp']=$otp;
            }
            $response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
            
        }
        
    }
    public function forgot_new_password()
    {
        //kullanıcı otp, eposta ve yeni şifresini gönderecek
        //session ile kullanıcı doğrulaması yapılabilie
        $body = (array)json_decode($this->input->raw_input_stream) ?? [];
        $post = $this->input->post() ?? [];
        $get = $this->input->get() ?? [];
        $params=[];
        if(!empty($post))$params = $post;
        if(!empty($body))$params = $body;
        if(!empty($get))$params = $get;
        if(empty($_SESSION['forgot_password']) || empty($params['otp'])|| $params['otp'] != $_SESSION['forgot_password']){
            res_error(["message"=>"otp_error","status"=>"error"]);
            
        }
        $response=[
            "required"=>[],
            "message"=>"",
            "status"=>"success"
        ];
        if(empty($params['email'])){
            array_push($response['required'],'email');
            $response['status']='error';
            $response['message']="required_error";
        }
        if(empty($params['password'])){
            array_push($response['required'],'password');
            $response['status']='error';
            $response['message']="required_error";
        }
        if(empty($params['password_verification'])){
            array_push($response['required'],'password_verification');
            $response['status']='error';
            $response['message']="required_error";
        }
        if(empty($params['password_verification']) || $params['password_verification'] != $params['password']){
            $response['message']='password_not_verified';
            $response['status']='error';
        }
        if($response['status']=='error'){
            res_error($response);
            
        }

        $response['status'] = ad_update('users','email:'.$params['email'],['password'=>$params['password']])?"success":"error";
        if($response['status']=='success'){
            unset($response['required']);
        }
        $response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
        
    }
    public function change_email()
    {
        get_user();
        //kullanıcıya otp gönder
        if($this->input->method()=='get'){
            
            $fields['email'] = ad_show('fields',"name:email");
            echo json_encode($fields);
            die();
        }else{
            $body = (array)json_decode($this->input->raw_input_stream) ?? [];
            $post = $this->input->post() ?? [];
            $params=[];
            if(!empty($post))$params = $post;
            if(!empty($body))$params = $body;
            
            $email = $this->def_email['change_email'];
            $otp="";
            $generator = "1357902468";
            for ($i = 1; $i <= 6; $i++) {
                $otp .= substr($generator, (rand()%(strlen($generator))), 1);
            }
            $_SESSION['change_email']=$otp;
            $title="";
            $content="";
            foreach (json_decode($email['title']) as $value) {
                $title .= "$value - ";
            }
            foreach (json_decode($email['content']) as $value) {
                $content .= str_replace("%otp_code%",$otp,$value)." <br/><br/><hr/><br/><br/> ";
            }
            $status= send_email($params['email'],$title,$content);
            $response['status']=$status?'success':$status;
            
            if($this->settings['dev_mode']){
                $response['otp']=$otp;
                $response['email']=$params['email'];
            }
            $response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
            
        }
    }
    public function change_new_email()
    {
        //kullanıcı yeni epostası ve otp göndersin
        //kullanıcı otp, eposta ve yeni şifresini gönderecek
        //session ile kullanıcı doğrulaması yapılabilie
        $body = (array)json_decode($this->input->raw_input_stream) ?? [];
        $post = $this->input->post() ?? [];
        $get = $this->input->get() ?? [];
        $params=[];
        if(!empty($post))$params = $post;
        if(!empty($body))$params = $body;
        if(!empty($get))$params = $get;
        if(empty($_SESSION['change_email']) || empty($params['otp'])|| $params['otp'] != $_SESSION['change_email']){
            res_error(["message"=>"otp_error","status"=>"error"]);
            
        }
        $response=[
            "required"=>[],
            "message"=>"",
            "status"=>"success"
        ];
        if(empty($params['email'])){
            array_push($response['required'],'email');
            $response['status']='error';
            $response['message']="required_error";
        }

        if($response['status']=='error'){
            res_error($response);
            
        }
        get_user();

        $response['status'] = ad_update('users','id:'.$this->input->user['id'],['email'=>$params['email']])?"success":"error";
        if($response['status']=='success'){
            unset($response['required']);
        }
        $response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
    }
    public function change_password()
    {
        get_user();
        if($this->input->method()=='get'){
            
        }
        //kullanıcı eski şifresi, yeni şifresi ve kontrol şifresi göndersin
        $body = (array)json_decode($this->input->raw_input_stream) ?? [];
        $post = $this->input->post() ?? [];
        $params=[];
        if(!empty($post))$params = $post;
        if(!empty($body))$params = $body;
        
        $response=[
            "required"=>[],
            "message"=>"",
            "status"=>"success"
        ];
        if(empty($params['old_password'])){
            array_push($response['required'],'old_password');
            $response['status']='error';
            $response['message']="required_error";
        }
        if(empty($params['new_password'])){
            array_push($response['required'],'new_password');
            $response['status']='error';
            $response['message']="required_error";
        }
        if(empty($params['new_password_verification'])){
            array_push($response['required'],'new_password_verification');
            $response['status']='error';
            $response['message']="required_error";
        }
        if(empty($params['new_password_verification']) || $params['new_password_verification'] != $params['new_password']){
            $response['message']='password_not_verified';
            $response['status']='error';
        }
        if($params['old_password'] != $this->input->user['password']){
            $response['message']='wrong_password';
            $response['status']='error';
        }

        if($response['status']=='error'){
            res_error($response);
        }
        unset($response['required']);
        unset($response['message']);
        

        $response['status'] = ad_update('users','id:'.$this->input->user['id'],['password'=>$params['new_password']])?"success":"error";
        if($response['status']=='success'){
            unset($response['required']);
        }
        $response['status'] == 'success'?res_success($response):res_error(["message"=>"error","status"=>"error"]);
    }
}