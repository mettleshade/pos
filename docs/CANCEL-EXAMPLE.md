
### Ödeme İptali

```sh
$ cp ./vendor/mews/pos/config/pos_test.php ./pos_test_ayarlar.php
```

**config.php (Ayar dosyası)**
```php
<?php
require './vendor/autoload.php';

$paymentModel = \Mews\Pos\PosInterface::MODEL_NON_SECURE;

// API kullanıcı bilgileri
// AccountFactory'de kullanılacak method Gateway'e göre değişir. Örnek kodlara bakınız.
$account = \Mews\Pos\Factory\AccountFactory::createEstPosAccount(
    'akbank', //pos config'deki ayarın index name'i
    'yourClientID',
    'yourKullaniciAdi',
    'yourSifre',
    $paymentModel
    '', // bankaya göre zorunlu
    PosInterface::LANG_TR
);

$eventDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher();

try {
    $config = require __DIR__.'/pos_test_ayarlar.php';

    $pos = \Mews\Pos\Factory\PosFactory::createPosGateway($account, $config, $eventDispatcher);

    // GarantiPos ve KuveytPos'u test ortamda test edebilmek için zorunlu.
    $pos->setTestMode(true);
} catch (\Mews\Pos\Exceptions\BankNotFoundException | \Mews\Pos\Exceptions\BankClassNullException $e) {
    var_dump($e));
    exit;
}
```

**status.php (kullanıcıdan kredi kart bilgileri alındıktan sonra çalışacak kod)**
```php
<?php

require 'config.php';

function createCancelOrder(string $gatewayClass, array $lastResponse, string $ip): array
{
    $cancelOrder = [
        'id'          => $lastResponse['order_id'], // MerchantOrderId
        'currency'    => $lastResponse['currency'],
        'ref_ret_num' => $lastResponse['ref_ret_num'],
        'ip'          => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $ip : '127.0.0.1',
    ];

    if (\Mews\Pos\Gateways\GarantiPos::class === $gatewayClass) {
        $cancelOrder['amount'] = $lastResponse['amount'];
    } elseif (\Mews\Pos\Gateways\KuveytPos::class === $gatewayClass) {
        $cancelOrder['remote_order_id'] = $lastResponse['remote_order_id']; // banka tarafındaki order id
        $cancelOrder['auth_code']       = $lastResponse['auth_code'];
        $cancelOrder['trans_id']        = $lastResponse['trans_id'];
        $cancelOrder['amount']          = $lastResponse['amount'];
    } elseif (\Mews\Pos\Gateways\PayFlexV4Pos::class === $gatewayClass || \Mews\Pos\Gateways\PayFlexCPV4Pos::class === $gatewayClass) {
        // çalışmazsa $lastResponse['all']['ReferenceTransactionId']; ile denenmesi gerekiyor.
        $cancelOrder['trans_id'] = $lastResponse['trans_id'];
    } elseif (\Mews\Pos\Gateways\PosNetV1Pos::class === $gatewayClass || \Mews\Pos\Gateways\PosNet::class === $gatewayClass) {
        /**
         * payment_model:
         * siparis olusturulurken kullanilan odeme modeli
         * orderId'yi dogru şekilde formatlamak icin zorunlu.
         */
        $cancelOrder['payment_model'] = $lastResponse['payment_model'];
        // satis islem disinda baska bir islemi (Ön Provizyon İptali, Provizyon Kapama İptali, vs...) iptal edildiginde saglanmasi gerekiyor
        // 'transaction_type' => $lastResponse['transaction_type'],
    }


    if (isset($lastResponse['recurring_id'])
        && \Mews\Pos\Gateways\EstPos::class === $gatewayClass || \Mews\Pos\Gateways\EstV3Pos::class === $gatewayClass
    ) {
        // tekrarlanan odemeyi iptal etmek icin:
        $cancelOrder = [
            'recurringOrderInstallmentNumber' => 1, // hangi taksidi iptal etmek istiyoruz?
        ];
    }

    return $cancelOrder;
}

// odemeden aldiginiz cevap: $pos->getResponse();
$lastResponse = $session->get('last_response');
$ip = '127.0.0.1';
$order = createCancelOrder(get_class($pos), $lastResponse, $ip);

$pos->cancel($order);
$response = $pos->getResponse();
dump($response);
```