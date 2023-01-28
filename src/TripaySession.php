<?php

namespace Hanoivip\PaymentMethodTripay;

use Hanoivip\PaymentMethodContract\IPaymentSession;

class TripaySession implements IPaymentSession
{
    private $trans;
    
    private $channels;
    
    public function __construct($trans, $channels)
    {
        $this->trans = $trans;
        $this->channels = $channels;
    }
    
    public function getSecureData()
    {}
    
    public function getGuide()
    {
        return __('hanoivip::tripay.guide');
    }
    
    public function getData()
    {
        return $this->channels;
    }
    
    public function getTransId()
    {
        return $this->trans->trans_id;
    }
    
}