<?php
require_once 'c:\vendor\autoload.php';

/**
* Linebot
*/
class Linebot {

	// var
		private $url ='https://api.line.me';
		private $channel_token = '2MCOyCeaBipmw3ZzJG8BrsiO4KzCoaoPddMgbZtEu5HHVeIaWU+PDKcCZRJEY76zqxv56d15kZeMoU/vQ0zuzPFlbhFM7AhRMZwLrSkLdciLCuKUgV6aFrvAAuuG1mMWe7DCzfEW9FfHQhJR4F/m0AdB04t89/1O/w1cDnyilFU=';
		private $channel_secret = 'd4afd7da941ac195c155fe67dcb5a338';
		private $content;
		private $data;
		private $type_grouproom;
		private $groupid;
		public  $maxleng = 1000;
		private $maxmsgcount = 5;

		private $event_replyToken;
		private $event_type;
		private $event_timestamp;
		private $event_source;



	public function __construct(){
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->pathinfo = $_SERVER['PATH_INFO'];
		if($this->method == 'POST' && $this->pathinfo == '/callback'){
			$this->content = json_decode(file_get_contents('php://input'));
			$this->Webhooks();
		} else {
			echo "I'm a LINE-BOT";
		}
	}
	
	// Webhooks – ใช้รับ Notification ที่เกิดขึ้นกับ LINE account แบบ realtime สามารถเอามา ทำ LINE Bot รับ message ได้
	public function Webhooks(){
		echo 'webhook';
		if($this->content && isset($this->content->events)){
			foreach ($this->content->events as $event) {
				$this->event = $event;
				$this->event_replyToken  = $event->replyToken;
				$this->event_type  = $event->type;
				$this->event_timestamp  = $event->timestamp;
				$this->event_source  = $event->source;
				switch ($event->type) {
					case 'message':
						$this->event_message();
						break;
					case 'follow':
						$this->event_follow();
						break;
					case 'unfollo':
						$this->event_unfollo();
						break;
					case 'join':
						$this->event_join();
						break;
					case 'leave':
						$this->event_leave();
						break;
					case 'postback':
						$this->event_postback();
						break;
					default:
							echo "I'm a LINE-BOT";
					    break;
				}
			}
		}
	}

	// Reply message – ตอบ message กลับไปหา user ที่ส่ง message มา
	private function Reply(){
		$this->post = json_encode($this->data);
		$this->headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $this->channel_token);
		$url = $this->url.'/v2/bot/message/reply';
		$this->curl_post($url);
	}
	// Push message  – ส่ง message หา user ได้ตลอดเวลา
	private function Push(){
		$this->post = json_encode($this->data);
		$this->headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $this->channel_token);
		$url = $this->url.'/v2/bot/message/push';
		$this->curl_post($url);
	}
	
	// Multicast – ส่ง message หาหลายๆ user พร้อมกัน (broadcast)
	// POST https://api.line.me/v2/bot/message/multicast
	// IDs of the receivers
	// Max: 150 users 	Messages Max: 5
	private function Multicast(){
		$this->post = json_encode($this->data);
		$this->headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $this->channel_token);
		$url = $this->url.'/v2/bot/message/multicast';
		$this->curl_post($url);
	}	

	// Content – download รูป, video และ ข้อความเสียงที่ user ส่งมา
	// GET https://api.line.me/v2/bot/message/{messageId}/content
	public function Content($messageId){
		$url = $this->url.'/v2/bot/message/'.$messageId.'/content';
		$this->curl_get($url);		
	}
	
	// Profile – ดึงข้อมูล user profile
	// GET https://api.line.me/v2/bot/profile/{userId}
	private function Profile($userid){
		$url = $this->url.'/v2/bot/profile/'.$userid;
		return $this->curl_get($url);
	}

	// Get group/room member profile
	// GET https://api.line.me/v2/bot/group/{groupId}/member/{userId}
	// GET https://api.line.me/v2/bot/room/{roomId}/member/{userId}
	private function GRprofile(){
		if($this->type_grouproom && $this->groupid ){
			$url = $this->url.'/v2/bot/'.$this->type_grouproom.'/'.$this->groupid.'/member'.$userId;
			$this->curl_get($url);
		}
	}

	// Leave – ออกจาก group/room
	// POST https://api.line.me/v2/bot/group/{groupId}/leave
	// POST https://api.line.me/v2/bot/room/{roomId}/leave
	private function Leave(){
		if($this->type_grouproom && $this->groupid){
			$url = $this->url.'/v2/bot/'.$this->type_grouproom.'/'.$this->groupid.'/leave';
			$this->curl_post($url);
		}

	}

	//verify
	private function verify(){
		$url = $this->url.'/v1/oauth/verify';
		$this->curl_get($url);
	}

	private function curl_get($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		$this->result = $result;
		return $result;
	}
	private function curl_post($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		$this->result = $result;
		return $result;
	}

	private function event_message() {
		echo 'message';
		$this->message = $this->event->message;
		switch ($this->message->type) {
			case 'text':
				$o = new stdClass();
					$o->id =  $this->message->id;
        			$o->type =  $this->message->type;
        			$o->text =  $this->message->text;
				break;
			case 'image':
			case 'video':
			case 'audio':
					$messageId = $this->message->id;
					$this->Content($messageId);
				break;
			case 'file':
				$o = new stdClass();
					$o->id = $this->message->id;
					$o->type = $this->message->type;
					$o->fileName = $this->message->fileName;
					$o->fileSize = $this->message->fileSize;
				break;
			case 'location':
				$o = new stdClass();
					$o->id = $this->message->id;
					$o->type = $this->message->type;
					$o->title = $this->message->title;
					$o->address = $this->message->address;
					$o->latitude = $this->message->latitude;
					$o->longitude = $this->message->longitude;
					dump($o);
				break;
			case 'sticker':
				$o = new stdClass();
					$o->id = $this->message->id;
        			$o->type = $this->message->type;
        			$o->packageId = $this->message->packageId;
        			$o->stickerId = $this->message->stickerId;
        		dum($o);
				break;
			default:
				break;
			}		
	}
	private function event_follow() {
		echo 'follow';
		$o = new stdClass();
		$o->userId = $this->event_source->userId;
		$o->profile =$this->Profile($o->userId);
			//ตัวอย่างไว้ map user กับ line id เพื่ออนาคตในการ push message
				   $messages = [
						'type' => 'text',
						'text' => 'http://www.yourweb.com/getinfo/'.$o->userId; 
						];
					$this->data = [
						'replyToken' => $this->event_replyToken,
						'messages' => [$messages],
					];
		$this->Reply();
	}
	private function event_unfollo() {
		echo 'unfollo';
		// save userId to Unfollow
		// $this->event_source->userId;
		//  {
		//    "type": "unfollow",
		//    "timestamp": 1462629479859,
		//    "source": {
		//         "type": "user",
		//         "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
		//     }
		// }
	}
	private function event_join() {
		echo 'join';
		  // {
		  //        "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
		  //       "type": "join",
		  //        "timestamp": 1462629479859,
		  //        "source": {
		  //             "type": "group",
		  //             "groupId": "cxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
		  //         }
		  //     }
	}
	private function event_leave() {
		echo 'leave';
		// {
		//  "type": "leave",
		//  "timestamp": 1462629479859,
		//  "source": {
		//       "type": "group",
		//       "groupId": "cxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
		//   }
		// }
	}
	private function event_postback() {
		echo 'postback';

	}

	public function  samplePushMessage(){
		// curl -X POST \
		// -H 'Content-Type:application/json' \
		// -H 'Authorization: Bearer {ENTER_ACCESS_TOKEN}' \
		// -d '{
		// 	"to": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
		// 	"messages":[
		// 		{
		// 			"type":"text",
		// 			"text":"Hello, user"
		// 		},
		// 		{
		// 			"type":"text",
		// 			"text":"May I help you?"
		// 		}
		// 	]
		// }' https://api.line.me/v2/bot/message/push
		$userId = 'userId you save in you system';
		$messages = [
				'type' => 'text',
				'text' => 'you message'; 
		];
		$this->data = [
			'to' => $userId,
			'message' =>[$messages]
		]
		$this->Push();
	}

}

$lb = new Linebot();