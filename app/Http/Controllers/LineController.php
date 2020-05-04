<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class LineController extends Controller
{
    private $channel_access_token;
    private $channel_secret;
    private $bot;
    private $client;
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
        }
    }
}
