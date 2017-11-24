<?php
// require_once 'c:\vendor\autoload.php';


class Linenoservice  {

	private $client_id = 'cvvUjCUM1LBLaXnIFSv8up';
	private $client_secret = 'xqQQf9ZOOAcihJr2Vo6OiBBjLTg5UydmaVpS1USADXj';
	private $redirect_uri;
	private $line_api = 'https://notify-bot.line.me/oauth/authorize?';
	private $linenotify = 'https://notify-api.line.me/api/notify';
	private $linuauthtoken = 'https://notify-bot.line.me/oauth/token';
	private $result;
	private $error;

	public function __construct(){
	 	$this->redirect_uri =$_SERVER['HTTP_HOST'].'index.php/callback';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') { //HTTPS } 
			$this->redirect_uri = 'https://'.$this->redirect_uri;
		} else {
			$this->redirect_uri = 'http://'.$this->redirect_uri;
		}
		if(isset($_SERVER['PATH_INFO']) &&  $_SERVER['PATH_INFO'] == '/callback') {
			// echo 'callback';
			$this->callback();
		} else 	if(isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] == '/sends') {
			$this->sends();
		// } else 	if(isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] == '/msg') {
		// 	echo 'msg';
		} else {
			$this->register();
		}
		//dump($this);
	}


	private function register(){
		$queryStrings = [
		    'response_type' => 'code',
		    'client_id' => $this->client_id,
		    'redirect_uri' => $this->redirect_uri,
		    'scope' => 'notify',
		    'state' => 'csrf'
		];

		$queryString = $this->line_api . http_build_query($queryStrings);
		echo '<a href="'.$queryString.'">subscribe เพื่อรับข้อความจาก Line Notify</a>';
	}

	private function callback() {
		$code = $_GET['code'];
		$state = $_GET['state'];
		$data = 'grant_type=authorization_code&code='.$code.
		'&redirect_uri='.$this->redirect_uri.
		'&client_id='.$this->client_id.
		'&client_secret='.$this->client_secret;
		$headers = array('content-type: application/x-www-form-urlencoded');
		$chOne = curl_init(); 
		curl_setopt( $chOne, CURLOPT_URL, $this->linuauthtoken); 
		curl_setopt( $chOne, CURLOPT_POST, 1); 
		curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt( $chOne, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt( $chOne, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt( $chOne, CURLOPT_POSTFIELDS, $data); 
		curl_setopt( $chOne, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt( $chOne, CURLOPT_RETURNTRANSFER, 1); 
		$this->result = json_decode(curl_exec( $chOne ),false); 
		//dump($this->result);
		if(curl_error($chOne)) { 
			$this->error =  curl_error($chOne); 
		} 
		curl_close( $chOne ); 
		$this->testnotify($this->result);
	}

	private function testnotify($result){
		//dump($this);
		//dump($_GET);
		//dump($_POST);
		//dump($result);
		$str = "ข้อความทดสอบจาก LCRM ส่งถึงท่าน : " . json_encode($this->result); //ข้อความที่ต้องการส่ง สูงสุด 1000 ตัวอักษร
		//dump($str);
		$stickerPkg = 2; //stickerPackageId
		$stickerId = 34; //stickerId
		// dump('--------------1---------------------');
		$ln = new Linenotify();
		$ln->message = $str;
		// $ln->image	            = 'https://i.imgur.com/FgeCASa.png';
		// $ln->stickerPackageId	= $stickerPkg;
		// $ln->stickerId	        = $stickerId;
		$rs = $ln->send();
		// dump($rs);

		// dump('--------------2---------------------');
		$myFile = "mytoken.txt";
		file_put_contents($myFile,$this->result->access_token.PHP_EOL, FILE_APPEND);
		// $myFileContents = file_get_contents($myFile);
		// echo $myFileContents;
		$ln = new Linenotify($this->result->access_token);
		$ln->message = $str;
		$rs = $ln->send();
		// dump($rs);
		echo 'OK';
	}

	private function sends(){
	 echo '
		<form action="index.php/sends" method="POST" accept-charset="utf-8">
		<input type="message" name="message"  placeholder="ข้อควาที่จะส่งถึงกลุ่ม">
		<input type="submit" name="submit" value="ส่งข้อความ">
		</form>';
		if(isset($_POST['message'])){
			$myFile = "mytoken.txt";
			$myFileContents = file_get_contents($myFile);
			$rs = explode(PHP_EOL,$myFileContents);
			// dump($rs);
			foreach ($rs as $r ) {
				if($r){
					$str = $_POST['message'];
					$ln = new Linenotify($r);
					$ln->message = $str;
					$rs = $ln->send();
					// dump($rs);
				}
			}
		}
	}

}

class Linenotify  {
	private $curl = '';
	private $url = 'https://notify-api.line.me/api/notify';	
	private $token = 'WJBGD9HkINjarKGafuGbM3WCDdaZ0YdgveExsmKGiFv';
	// private $token = 'lgEdCHS5HK4KwZYfyYQKELeZqLQpkG3J63bs2YADpAa'; //techexchange
	// private $token = 'fR5PHKobjQagnPefdG5DzZOKILO3Pe3SozfWXSpekjK'; //test notify
	// private $token = 'iYh9W1S0SpZe1L1gBDxY7eW6zeJSI9cyTyhgfTAkG5I'; //line notify lcrm service
	private $header = '';
	private $post = '';
	private $result = '';

	public  $maxlengthnotify = 1000;
	public  $message =  ' ';
	public  $stickerPackageId = '';
	public  $stickerId = '';
	public  $image = '';

	public function __construct($token=''){
		($token ? $this->token = $token : null );
		$this->headers = ['Content-type: application/x-www-form-urlencoded','Authorization: Bearer '.$this->token,]; 
		$this->init();
	}

	private function init(){
		$this->curl = curl_init($this->url);
		curl_setopt( $this->curl, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt( $this->curl, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt( $this->curl, CURLOPT_POST, 1); 
		curl_setopt( $this->curl, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt( $this->curl, CURLOPT_HTTPHEADER, $this->headers); 
		curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, 1); 
	}

	private function queryurl($post=[]) {
		if($post){
			$this->post = rawurldecode(http_build_query($post));
		} else {
			$posts = [];
			if($this->stickerPackageId && $this->stickerId ){
				$posts['stickerPackageId'] = $this->stickerPackageId;
				$posts['stickerId'] = $this->stickerId;
			}
			if($this->image){
				$posts['imageFullsize'] = $this->image;
				$posts['imageThumbnail'] = $this->image;
			}
			$posts['message'] = $this->message?:'';
			if($posts) {
				$this->post = rawurldecode(http_build_query($posts));
			}
		}
	}

	public function send($post=''){
		$rs = new stdClass();
		$rs->status = -1; // -1 message is null   0 cannot sent is have error    1 send successed
		if(mb_strlen($this->message) > $this->maxlengthnotify ){
			$rs->status = 0;
			$rs->error = 'message maxlength > '.$this->maxlengthnotify;	
		}
		if($this->token){
			$this->queryurl($post);
			curl_setopt( $this->curl, CURLOPT_POSTFIELDS,$this->post); 
			$result = curl_exec( $this->curl ); 
			if(curl_error($this->curl)) { 
				$rs->status = 0;
				$rs->error = curl_error($this->curl); 
			} else {
				$rs->status = 1;
				$rs->result = json_decode($result);
			}
			curl_close( $this->curl ); 
		} else {
			$rs->staus = 0;
			$rs->error = 'no token';	
		}
		$this->result = $rs;
		return $rs;
	}

	public function __toString() {
		return json_encode($this->result);
	}
}

$ln = new Linenoservice();
