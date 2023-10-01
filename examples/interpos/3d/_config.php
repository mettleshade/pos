<?php

use Mews\Pos\PosInterface;

require '../_payment_config.php';

$baseUrl = $bankTestsUrl.'/3d/';
//account bilgileri kendi account bilgilerinizle degistiriniz
$userCode =  'InterTestApi';
$userPass = '3';
$shopCode = '3123';
$merchantPass = 'gDg1N';
$account = \Mews\Pos\Factory\AccountFactory::createInterPosAccount(
    'denizbank',
    $shopCode,
    $userCode,
    $userPass,
    PosInterface::MODEL_3D_SECURE,
    $merchantPass,
    PosInterface::LANG_TR
);

$pos = getGateway($account, $eventDispatcher);

$transaction = PosInterface::TX_PAY;

$templateTitle = '3D Model Payment';
$paymentModel = PosInterface::MODEL_3D_SECURE;
