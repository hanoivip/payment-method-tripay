<?php

namespace Hanoivip\PaymentMethodTripay;

use Hanoivip\PaymentMethodContract\IPaymentResult;

class TripayFailure implements IPaymentResult
{
    private $detail;
    
    private $error;
    /**
     *
     * @param array $detail Tripay transaction detail
     * https://tripay.co.id/developer?tab=merchant-transactions
     */
    function __construct($tripayTrans, $error)
    {
        $this->detail = $tripayTrans;
        $this->error = $error;
    }
    
    public function getDetail()
    {
        return $this->error;
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
        return false;
    }

    public function isFailure()
    {
        return true;
    }

    public function getTransId()
    {
        return $this->detail['data']['merchant_ref'];
    }

    public function isSuccess()
    {
        return false;
    }

    public function getAmount()
    {
        return 0;
    }
    
    public function getCurrency()
    {
        return 'IDR';
    }


}