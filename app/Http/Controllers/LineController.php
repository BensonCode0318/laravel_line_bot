<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\JoinEvent;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use GuzzleHttp\Client;


class LineController extends Controller
{
    private $channel_access_token;
    private $channel_secret;
    private $bot;
    private $client;
    private $groupId;
    public function __construct()
    {
        $this->channel_access_token = env('LINEBOT_TOKEN');
        $this->channel_secret = env('LINEBOT_SECRET');

        $httpClient   = new CurlHTTPClient($this->channel_access_token);
        $this->bot    = new LINEBot($httpClient, ['channelSecret' => $this->channel_secret]);
        $this->client = $httpClient;
    }

    /*public function pushMessage($content): Response
    {
        if(is_string($content)){
            $content = new TextMessageBuilder($content);
        }
        return $this->LineBot->pushMessage($this->lineUserId, $content);
    }
    */

    public function webhook(Request $request)
    {
        $bot       = $this->bot;
        $signature = $request->header(\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE);
        $body      = $request->getContent();

        try {
            $events = $bot->parseEventRequest($body, $signature);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        foreach ($events as $event) {
            $replyToken = $event->getReplyToken();
            if ($event instanceof MessageEvent) {
                $message_type = $event->getMessageType();
                $text = $event->getText();
                switch ($message_type) {
                    case 'text':
                        $bot->replyText($replyToken, 'Hello world!');
                        break;
                }
            }

            if($event instanceof JoinEvent){
                $this->groupId = $event->getGroupId();
                $bot->replyText($replyToken,$this->groupId);
                $this->notify($this->groupId);
            }
        }
    }

    public function notify($id)
    {
        $bot = $this->bot;
        #$id = $this->groupId;
        #$id = 'Cbcd11a78d69acdaa3b8d47981fe3abd2';
        $message = '門口有體溫異常的客人喔，請前往查看！';
        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
        $response = $bot->pushMessage($id, $textMessageBuilder);

        /*
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST','https://api.line.me/v2/bot/message/push',[
            'headers'=>[
                'Authorization'=>'Bearer Rn9+S4/8ocj6jEjwwHTtmYkmiSF9uHJ1pVx1TV3071zrjAw5YjthxZkwe7hwdVKIQHmT/kD4NPl6wNbzJ6wmE3l+N8ZgmUSi4B1GbvgfXIWt4Q2rvqIV4KyhPekYQRrGFvagclcaTY4mcSheKx8xgQdB04t89/1O/w1cDnyilFU=',
                'Content-Type'=>'application/json'
            ],
            'json' =>[
                'to'=>'U217edfc99fef29581aac21d6e6577f6b',
                'messages'=>[
                    [
                        "type"=>"text",
                        "text"=>"測試推播"
                    ]
                ]
            ]
        ]);
        */
    }
}
