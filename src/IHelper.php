<?php

namespace Hanoivip\PaymentMethodTripay;

interface IHelper
{
    public function setConfig($cfg);
    /**
     * @return NULL|array Null if failure
     */
    public function listChannels();
    
    public function create($merchantRef, $channel, $order);
    /**
     * 
     * @param string $code Channel code
     * @return NULL|array
     */
    public function instruct($code);
    
    public function fetch($ref);
}