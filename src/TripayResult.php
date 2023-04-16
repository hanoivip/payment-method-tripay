<?php

namespace Hanoivip\PaymentMethodTripay;

use Hanoivip\PaymentMethodContract\IPaymentResult;

class TripayResult implements IPaymentResult
{
    private $detail;
    
    private $channelInstruct;
    
    /**
     * 
     * @param array $detail Tripay transaction detail
     * https://tripay.co.id/developer?tab=merchant-transactions
     */
    function __construct($tripayTrans, $instructions = null)
    {
        $this->detail = $tripayTrans;
        $this->channelInstruct = $instructions;
    }
    public function getDetail()
    {
        $instructs = [];
        $qr = '';
        if (isset($this->detail['instructions']))
        {
            $instructs = $this->detail['instructions'];
            if (isset($this->detail['qr_url']) && 
                !empty($this->detail['qr_url']))
                $qr = $this->detail['qr_url'];
        }
        else
            $instructs = $this->channelInstruct;
        return ['guide' => $instructs, 'amount' => $this->getAmount(), 'qr' => $qr];
    }

    public function toArray()
    {
        $arr = [];
        $arr['detail'] = $this->getDetail();
        $arr['amount'] = $this->getAmount();
        $arr['isPending'] = $this->isPending();
        $arr['isFailure'] = $this->isFailure();
        $arr['isSuccess'] = $this->isSuccess();
        $arr['trans'] = $this->getTransId();
        return $arr;
    }

    public function isPending()
    {
        return $this->detail['status'] == 'UNPAID';
    }

    public function isFailure()
    {
        return $this->detail['status'] == 'CANCEL';
    }

    public function getTransId()
    {
        return $this->detail['merchant_ref'];
    }

    public function isSuccess()
    {
        return $this->detail['status'] == 'PAID';
    }

    public function getAmount()
    {
        return $this->detail['amount'];
    }
    
    public function getCurrency()
    {
        return 'IDR';
    }


}