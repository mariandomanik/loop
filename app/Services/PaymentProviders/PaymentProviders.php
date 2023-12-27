<?php

namespace App\Services\PaymentProviders;

interface PaymentProviders
{
    public function pay();

    public function processResponse();
}