<?php

namespace Hanoivip\PaymentMethodTripay;

use Illuminate\Support\Facades\Log;
use Mervick\CurlHelper;
use AmrShawky\LaravelCurrency\Facade\Currency;

class TripayApi implements IHelper
{
    // key = sandbox
    const END_POINT = [true => 'https://tripay.co.id/api-sandbox', false => 'https://tripay.co.id/api'];
    
    private $sandbox = true;
    private $merchantId;
    private $apiKey;
    private $privateKey;
    
    public function setConfig($cfg)
    {
        $this->sandbox = $cfg['sandbox'];// config('tripay.sandbox', true);
        $this->merchantId = $cfg['merchant_id'];// config('tripay.merchant_id');
        $this->apiKey = $cfg['api_key'];// config('tripay.api_key');
        $this->privateKey = $cfg['private_key'];// config('tripay.private_key');
    }
    
    public function listChannels()
    {
        $url = self::END_POINT[$this->sandbox] . "/merchant/payment-channel";
        $response = CurlHelper::factory($url)
        ->setHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
        ->exec();
        if ($response['status'] == 200 && 
            !empty($response['data']) &&
            $response['data']['success'])
        {
            return $response['data']['data'];
        }
    }

    public function instruct($code)
    {
        $url = self::END_POINT[$this->sandbox] . "/payment/instruction?" . http_build_query(['code' => $code]);
        $response = CurlHelper::factory($url)
        ->setHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
        ->exec();
        if ($response['status'] == 200 &&
            !empty($response['data']) &&
            $response['data']['success'])
        {
            return $response['data']['data'];
        }
    }

    public function fetch($ref)
    {
        $url = self::END_POINT[$this->sandbox] . "/transaction/detail?" . http_build_query(['reference' => $ref]);
        $response = CurlHelper::factory($url)
        ->setHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
        ->exec();
        if ($response['status'] == 200 &&
            !empty($response['data']) &&
            $response['data']['success'])
        {
            return $response['data']['data'];
        }
    }

    public function create($merchantRef, $channel, $order)
    {
        $url = self::END_POINT[$this->sandbox] . "/transaction/create";
        /*
        $amount = Currency::convert()
                    ->from('USD')
                    ->to('IDR')
                    ->amount($order['item_price'])
                    ->get();
                    */
        $amount = $order['item_price'];//TODO: currency? 
        $amount = intval($amount);
        Log::error("$this->merchantId $merchantRef $amount  $this->privateKey");
        Log::error(hash_hmac('sha256', $this->merchantId.$merchantRef.$amount, $this->privateKey));
        $params = [
            'method' => $channel['code'],
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => 'Admin',
            'customer_email' => 'game.oh.vn@gmail.com',
            'customer_phone' => '+84365362826',
            'order_items' => [
                [
                    'sku' => $order['item'],
                    'name' => $order['item'],
                    'price' => $amount,
                    'quantity' => 1,
                    'product_url' => '#',
                    'image_url' => '#'
                ]
            ],
            //'callback_url' => route('tripay.callback'),
            //'return_url' => route('tripay.return'),
            //expired_time
            'signature' => hash_hmac('sha256', $this->merchantId.$merchantRef.$amount, $this->privateKey)
        ];
        $response = CurlHelper::factory($url)
        ->setPostParams($params)
        ->setHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
        ->exec();
        if ($response['status'] == 200 &&
            !empty($response['data']))
        {
            if ($response['data']['success'])        
            {
                return $response['data']['data'];
            }
            else
            {
                return $response['data']['message'];
            }
        }
        else
        {
            Log::error("Tripay create transaction error: " . print_r($response['data'], true));
        }
    }

    
}