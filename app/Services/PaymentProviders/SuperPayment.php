<?php

namespace App\Services\PaymentProviders;

class SuperPayment implements PaymentProviders
{
    protected const URL = 'https://superpay.view.agentur-loop.com/pay';
    protected const RESPONSE_OK = 'Payment Successful';
    protected const RESPONSE_INSUFFICIENT_FUNDS = 'Insufficient Funds';
    protected const SERVICE_UNAVAILABLE = 'Service Unavailable';

    public function pay()
    {

    }

    public function processResponse()
    {

    }
}