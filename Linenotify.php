<?php

class Linenotify  {
	private $curl = '';
	private $url = 'https://notify-api.line.me/api/notify';	
	// private $token = 'WJBGD9HkINjarKGafuGbM3WCDdaZ0YdgveExsmKGiFv';
	// private $token = 'lgEdCHS5HK4KwZYfyYQKELeZqLQpkG3J63bs2YADpAa'; //techexchange
	// private $token = 'fR5PHKobjQagnPefdG5DzZOKILO3Pe3SozfWXSpekjK'; //test notify
	private $token = 'iYh9W1S0SpZe1L1gBDxY7eW6zeJSI9cyTyhgfTAkG5I'; //line notify lcrm service
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

/* ---------------------------------------------------*/
// $token = "WJBGD9HkINjarKGafuGbM3WCDdaZ0YdgveExsmKGiFv"; //ใส่Token ที่copy เอาไว้
// $ln = new Linenotify($token);
// 
$str = "Hello ภาษาไทย "; //ข้อความที่ต้องการส่ง สูงสุด 1000 ตัวอักษร
$stickerPkg = 2; //stickerPackageId
$stickerId = 34; //stickerId
$ln = new Linenotify();
$ln->message	        = $str;
// $ln->image	            = 'https://i.imgur.com/FgeCASa.png';
// $ln->stickerPackageId	= $stickerPkg;
// $ln->stickerId	        = $stickerId;
$rs = $ln->send();
echo $ln;
var_dump($rs);
// ---------------------------------------------------


/* -----------------------------------------
object(stdClass)#2 (2) {
  ["status"]=>
  int(1)
  ["result"]=>
  string(29) "{"status":200,"message":"ok"}"
}
-------------------------------------------*/
