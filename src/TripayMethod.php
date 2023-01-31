<?php

namespace Hanoivip\PaymentMethodTripay;

use Hanoivip\PaymentMethodContract\IPaymentMethod;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\IapContract\Facades\IapFacade;

class TripayMethod implements IPaymentMethod
{
    private $config;
    
    private $helper;
    
    public function __construct(IHelper $helper)
    {
        $this->helper = $helper;
    }
    
    public function endTrans($trans)
    {
        session(['channels' => null]);
    }

    public function cancel($trans)
    {
        session(['channels' => null]);
    }

    public function beginTrans($trans)
    {
        $exists = TripayTransaction::where('trans', $trans->trans_id)->get();
        if ($exists->isNotEmpty())
            throw new Exception('Tripay transaction existed!');
        $channels = $this->helper->listChannels();
        $log = new TripayTransaction();
        $log->trans = $trans->trans_id;
        $log->save();
        session(['channels' => $channels]);
        return new TripaySession($trans, $channels);
    }
    
    private function getChannelDetail($channels, $code)
    {
        foreach ($channels as $c)
        {
            if ($c['code'] == $code)
                return $c;
        }
    }

    public function request($trans, $params)
    {
        $channels = session('channels');
        //Log::error(print_r($params, true));
        //Log::error(print_r($channels, true));
        if (!isset($params['channel']) || empty($channels))
        {
            return new TripayFailure($trans, __('tripay::tripay.timeout'));
        }
        $log = TripayTransaction::where('trans', $trans->trans_id)->first();
        $channel = $params['channel'];
        $channelDetail = $this->getChannelDetail($channels, $channel);
        if (empty($channelDetail))
        {
            return new TripayFailure($trans, __('tripay::tripay.error'));
        }
        try {
            $order = $trans->order;
            $orderDetail = IapFacade::detail($order);
            $this->helper->setConfig($this->config);
            $tripayTrans = $this->helper->create($trans->trans_id, $channelDetail, $orderDetail);
            // Log::error(print_r($tripayTrans, true));
            if (empty($tripayTrans))
            {
                return new TripayFailure($trans, __('tripay::tripay.error'));
            }
            // save
            $log->tripay = json_encode($tripayTrans);
            $log->save();
            $instruct = $this->helper->instruct($channel);
            //Log::error(print_r($instruct, true));
            if (empty($instruct))
            {
                return new TripayFailure($trans, __('tripay::tripay.error'));
            }
            return new TripayResult($tripayTrans, $instruct);
        } catch (Exception $ex) {
            Log::error("Tripay create transaction error: " . $ex->getMessage());
            Log::error(">>>>>>>> " . $ex->getTraceAsString());
            return new TripayFailure($trans, __('tripay::tripay.exception'));
        }
    }

    public function query($trans, $force = false)
    {
        $log = TripayTransaction::where('trans', $trans->trans_id)->first();
        if (empty($log))
        {
            return new TripayFailure($trans, __('tripay::tripay.failure.error'));
        }
        try
        {
            $oldDetail = json_decode($log->tripay, true);
            $detail = $this->helper->fetch($oldDetail['reference']);
            if (!empty($detail))
            {
                $log->tripay = json_encode($detail);
                $log->save();
                return new TripayResult($detail);
            }
        }
        catch (Exception $ex)
        {
            Log::error("Tripay query transaction exception " . $ex->getMessage());
        }
        $instruct = $this->helper->instruct($oldDetail['payment_method']);
        return new TripayResult($oldDetail, $instruct);
    }

    public function config($cfg)
    {
        //Log::debug("Tripay cfg " . print_r($cfg, true) );
        $this->config = $cfg;
        $this->helper->setConfig($cfg);
    }    
}