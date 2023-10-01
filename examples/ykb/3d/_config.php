<?php

use Mews\Pos\Factory\AccountFactory;
use Mews\Pos\PosInterface;

require '../_payment_config.php';

$baseUrl = $bankTestsUrl.'/3d/';
//account bilgileri kendi account bilgilerinizle degistiriniz
$account = AccountFactory::createPosNetAccount(
    'yapikredi',
    '6706598320',
    '67322946',
    '27426',
    PosInterface::MODEL_3D_SECURE,
    '10,10,10,10,10,10,10,10'
);

/**
 * vftCode: Vade Farklı işlemler için kullanılacak olan kampanya kodunu belirler.
 * Üye İşyeri için tanımlı olan kampanya kodu, İşyeri Yönetici Ekranlarına giriş
 * yapıldıktan sonra, Üye İşyeri bilgileri sayfasından öğrenilebilinir.
 * vtfCode set etmek icin simdilik bu sekilde:
 * $account->promotion_code = 'xxx';
 *
 * ilerde vtfCode atanmasi duzgun ele alinacak
 */

$pos = getGateway($account, $eventDispatcher);

$transaction = PosInterface::TX_PAY;

$templateTitle = '3D Model Payment';
$paymentModel = PosInterface::MODEL_3D_SECURE;
