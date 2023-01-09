<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');
class Account extends CI_Controller
{
    public $settings=[];
    public function __construct()
    {
        parent::__construct();
        session_start();
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
    public function register_columns()
    {
        $columns =  json_decode($this->settings['register_columns']);
        $this->load->library('../libraries/Baseback.php');
        $fields=[];
        foreach ($columns as $value) {
            $fields[$value] = $this->baseback->show("tr",'fields',"name:$value")['data'];
        }

        $fields['captcha'] =$this->captcha();
        echo json_encode($fields);
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
        
        if(empty($params["captcha"]) || md5($params["captcha"]) != $_SESSION['newsession']){
            $this->output
			->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(['error'=>"captcha_error","status"=>"error"]))
			->set_status_header(400)
			->_display();
            die();
        }

        $columns =  json_decode($this->settings['register_columns']);
        $response=[
            "error"=>["required"=>[]]
        ];
        foreach ($columns as  $value) {
            if(empty($params[$value])){
                array_push($response['error']['required'],$value);
                $response['status']="error";
            }
        }
        if($response['status']=="error"){
            $this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response))
			->set_status_header(400)
			->_display();
            die();
        }
        unset($params['captcha']);
        unset($response['error']);
        
        
        $this->load->model('base_model');
        $response['status'] =  $this->base_model->add('users',$params)?'success':'error';
        

        $this->output
			->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response))
			->set_status_header($response['status'] == 'success'?200:400)
			->_display();
            die();
    }
    public function captcha()
    {
        $image = @imagecreatetruecolor(120, 30) or die("hata oluştu");

		// arkaplan rengi oluşturuyoruz
		$background = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
		imagefill($image, 0, 0, $background);
		$linecolor = imagecolorallocate($image, 0xCC, 0xCC, 0xCC);
		$textcolor = imagecolorallocate($image, 0x33, 0x33, 0x33);

		// rast gele çizgiler oluşturuyoruz
		for ($i = 0; $i < 6; $i++) {
			imagesetthickness($image, rand(1, 3));
			imageline($image, 0, rand(0, 30), 120, rand(0, 30), $linecolor);
		}


		// rastgele sayılar oluşturuyoruz
		$sayilar = '';
		for ($x = 15; $x <= 95; $x += 20) {
			$sayilar .= ($sayi = rand(0, 9));
			imagechar($image, rand(3, 5), $x, rand(2, 14), $sayi, $textcolor);
		}

		// sayıları session aktarıyoruz

		
		/*session is started if you don't write this line can't use $_Session  global variable*/
		$_SESSION["newsession"] = md5($sayilar);


		// resim gösteriliyor ve sonrasında siliniyor
		//header('Content-type: image/png');
		ob_start();
		imagepng($image);
		$imagedata = ob_get_clean();

        return "data:image/jpg;base64,". base64_encode($imagedata);

		imagedestroy($image);
    }
    public function profile($lang)
    {
        
        
    }
}