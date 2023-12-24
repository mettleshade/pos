<?php
/**
 * @license MIT
 */

namespace Mews\Pos\Tests\Unit\DataMapper\ResponseDataMapper;

use Mews\Pos\DataMapper\RequestDataMapper\InterPosRequestDataMapper;
use Mews\Pos\DataMapper\ResponseDataMapper\InterPosResponseDataMapper;
use Mews\Pos\Factory\CryptFactory;
use Mews\Pos\Gateways\InterPos;
use Mews\Pos\PosInterface;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\NullLogger;

/**
 * @covers \Mews\Pos\DataMapper\ResponseDataMapper\InterPosResponseDataMapper
 */
class InterPosResponseDataMapperTest extends TestCase
{
    private InterPosResponseDataMapper $responseDataMapper;

    protected function setUp(): void
    {
        parent::setUp();

        $crypt                    = CryptFactory::createGatewayCrypt(InterPos::class, new NullLogger());
        $requestDataMapper        = new InterPosRequestDataMapper($this->createMock(EventDispatcherInterface::class), $crypt);
        $this->responseDataMapper = new InterPosResponseDataMapper(
            $requestDataMapper->getCurrencyMappings(),
            $requestDataMapper->getTxTypeMappings(),
            $requestDataMapper->getSecureTypeMappings(),
            new NullLogger()
        );
    }

    /**
     * @dataProvider paymentTestDataProvider
     */
    public function testMapPaymentResponse(array $order, string $txType, array $responseData, array $expectedData)
    {
        $actualData = $this->responseDataMapper->mapPaymentResponse($responseData, $txType, $order);
        unset($actualData['all']);
        $this->assertSame($expectedData, $actualData);
    }

    /**
     * @dataProvider threeDPaymentDataProvider
     */
    public function testMap3DPaymentData(array $order, string $txType, array $threeDResponseData, array $paymentResponse, array $expectedData)
    {
        $actualData = $this->responseDataMapper->map3DPaymentData(
            $threeDResponseData,
            $paymentResponse,
            $txType,
            $order
        );
        unset($actualData['all'], $actualData['3d_all']);
        \ksort($expectedData);
        \ksort($actualData);
        $this->assertSame($expectedData, $actualData);
    }

    /**
     * @dataProvider threeDPayPaymentDataProvider
     */
    public function testMap3DPayResponseData(array $order, string $txType, array $responseData, array $expectedData)
    {
        $actualData = $this->responseDataMapper->map3DPayResponseData($responseData, $txType, $order);
        unset($actualData['all'], $actualData['3d_all']);
        \ksort($expectedData);
        \ksort($actualData);
        $this->assertSame($expectedData, $actualData);
    }

    /**
     * @dataProvider threeDHostPaymentDataProvider
     */
    public function testMap3DHostResponseData(array $order, string $txType, array $responseData, array $expectedData)
    {
        $actualData = $this->responseDataMapper->map3DHostResponseData($responseData, $txType, $order);
        unset($actualData['all'], $actualData['3d_all']);
        \ksort($expectedData);
        \ksort($actualData);
        $this->assertSame($expectedData, $actualData);
    }

    /**
     * @dataProvider statusTestDataProvider
     */
    public function testMapStatusResponse(array $responseData, array $expectedData)
    {
        $actualData = $this->responseDataMapper->mapStatusResponse($responseData);
        unset($actualData['all']);
        $this->assertSame($expectedData, $actualData);
    }

    /**
     * @dataProvider refundTestDataProvider
     */
    public function testMapRefundResponse(array $responseData, array $expectedData)
    {
        $actualData = $this->responseDataMapper->mapRefundResponse($responseData);
        unset($actualData['all']);
        $this->assertSame($expectedData, $actualData);
    }

    /**
     * @dataProvider cancelTestDataProvider
     */
    public function testMapCancelResponse(array $responseData, array $expectedData)
    {
        $actualData = $this->responseDataMapper->mapCancelResponse($responseData);
        unset($actualData['all']);
        $this->assertSame($expectedData, $actualData);
    }

    public function threeDHashCheckDataProvider(): array
    {
        return [
            [
                'expectedResult' => true,
                'responseData'   => [
                    'Version'        => '',
                    'PurchAmount'    => 320,
                    'Exponent'       => '',
                    'Currency'       => '949',
                    'OkUrl'          => 'https://localhost/pos/examples/interpos/3d/success.php',
                    'FailUrl'        => 'https://localhost/pos/examples/interpos/3d/fail.php',
                    'MD'             => '',
                    'OrderId'        => '20220327140D',
                    'ProcReturnCode' => '81',
                    'Response'       => '',
                    'mdStatus'       => '0',
                    'HASH'           => '9DZVckklZFjuoA7sl4MN0l7VDMo=',
                    'HASHPARAMS'     => 'Version:PurchAmount:Exponent:Currency:OkUrl:FailUrl:MD:OrderId:ProcReturnCode:Response:mdStatus:',
                    'HASHPARAMSVAL'  => '320949https://localhost/pos/examples/interpos/3d/success.phphttps://localhost/pos/examples/interpos/3d/fail.php20220327140D810',
                ],
            ],
        ];
    }


    public function paymentTestDataProvider(): array
    {
        return
            [
                'fail1' => [
                    'order'        => [
                        'currency' => PosInterface::CURRENCY_TRY,
                        'amount'   => 1.01,
                    ],
                    'txType'       => PosInterface::TX_TYPE_PAY,
                    'responseData' => [
                        'OrderId'               => '20221225662C',
                        'ProcReturnCode'        => '81',
                        'HostRefNum'            => 'hostid',
                        'AuthCode'              => '',
                        'TxnResult'             => 'Failed',
                        'ErrorMessage'          => 'Terminal Aktif Degil',
                        'CampanyId'             => '',
                        'CampanyInstallCount'   => '0',
                        'CampanyShiftDateCount' => '0',
                        'CampanyTxnId'          => '',
                        'CampanyType'           => '',
                        'CampanyInstallment'    => '0',
                        'CampanyDate'           => '0',
                        'CampanyAmnt'           => '0',
                        'TRXDATE'               => '',
                        'TransId'               => '',
                        'ErrorCode'             => 'B810002',
                        'EarnedBonus'           => '0',
                        'UsedBonus'             => '0',
                        'AvailableBonus'        => '0',
                        'BonusToBonus'          => '0',
                        'CampaignBonus'         => '0',
                        'FoldedBonus'           => '0',
                        'SurchargeAmount'       => '0',
                        'Amount'                => '1,01',
                        'CardHolderName'        => '',
                    ],
                    'expectedData' => [
                        'order_id'         => '20221225662C',
                        'trans_id'         => null,
                        'transaction_type' => 'pay',
                        'currency'         => 'TRY',
                        'amount'           => 1.01,
                        'payment_model'    => 'regular',
                        'auth_code'        => null,
                        'ref_ret_num'      => 'hostid',
                        'proc_return_code' => '81',
                        'status'           => 'declined',
                        'status_detail'    => 'invalid_credentials',
                        'error_code'       => 'B810002',
                        'error_message'    => 'Terminal Aktif Degil',
                    ],
                ],
            ];
    }


    public function threeDPaymentDataProvider(): array
    {
        return [
            'authFail1' => [
                'order'              => [],
                'txType'             => PosInterface::TX_TYPE_PAY,
                'threeDResponseData' => [
                    'Version'                 => null,
                    'MerchantID'              => null,
                    'ShopCode'                => '3123',
                    'TxnStat'                 => 'N',
                    'MD'                      => null,
                    'RetCode'                 => null,
                    'RetDet'                  => null,
                    'VenderCode'              => null,
                    'Eci'                     => null,
                    'PayerAuthenticationCode' => null,
                    'PayerTxnId'              => null,
                    'CavvAlg'                 => null,
                    'PAResVerified'           => 'False',
                    'PAResSyntaxOK'           => 'False',
                    'Expiry'                  => '****',
                    'Pan'                     => '540061******0430',
                    'OrderId'                 => '20221225E1DF',
                    'PurchAmount'             => '1,01',
                    'Exponent'                => null,
                    'Description'             => null,
                    'Description2'            => null,
                    'Currency'                => '949',
                    'OkUrl'                   => 'http:\/\/localhost\/interpos\/3d\/response.php',
                    'FailUrl'                 => 'http:\/\/localhost\/interpos\/3d\/response.php',
                    '3DStatus'                => '0',
                    'AuthCode'                => null,
                    'HostRefNum'              => 'hostid',
                    'TransId'                 => null,
                    'TRXDATE'                 => null,
                    'CardHolderName'          => null,
                    'mdStatus'                => '0',
                    'ProcReturnCode'          => '81',
                    'TxnResult'               => null,
                    'ErrorMessage'            => 'Terminal Aktif Degil',
                    'ErrorCode'               => 'B810002',
                    'Response'                => null,
                    'HASH'                    => '423AWRAXl0VlEbQjpmAfntT5e3E=',
                    'HASHPARAMS'              => 'Version:PurchAmount:Exponent:Currency:OkUrl:FailUrl:MD:OrderId:ProcReturnCode:Response:mdStatus:',
                    'HASHPARAMSVAL'           => '1,01949http:\/\/localhost\/interpos\/3d\/response.phphttp:\/\/localhost\/interpos\/3d\/response.php20221225E1DF810',
                ],
                'paymentData'        => [],
                'expectedData'       => [
                    'order_id'             => '20221225E1DF',
                    'trans_id'             => null,
                    'auth_code'            => null,
                    'ref_ret_num'          => 'hostid',
                    'proc_return_code'     => '81',
                    'status'               => 'declined',
                    'status_detail'        => 'invalid_credentials',
                    'error_code'           => 'B810002',
                    'error_message'        => 'Terminal Aktif Degil',
                    'transaction_security' => 'MPI fallback',
                    'md_status'            => '0',
                    'masked_number'        => '540061******0430',
                    'month'                => null,
                    'year'                 => null,
                    'amount'               => 1.01,
                    'currency'             => 'TRY',
                    'eci'                  => null,
                    'tx_status'            => 'N',
                    'cavv'                 => null,
                    'md_error_message'     => 'Terminal Aktif Degil',
                    'transaction_type'     => 'pay',
                    'payment_model'        => '3d',
                ],
            ],
        ];
    }


    public function threeDPayPaymentDataProvider(): array
    {
        return [
            'authFail1' => [
                'order'        => [],
                'txType'       => PosInterface::TX_TYPE_PAY,
                'paymentData'  => [
                    'Version'                 => '',
                    'MerchantID'              => '',
                    'ShopCode'                => '3123',
                    'TxnStat'                 => 'N',
                    'MD'                      => '',
                    'RetCode'                 => '',
                    'RetDet'                  => '',
                    'VenderCode'              => '',
                    'Eci'                     => '',
                    'PayerAuthenticationCode' => '',
                    'PayerTxnId'              => '',
                    'CavvAlg'                 => '',
                    'PAResVerified'           => 'False',
                    'PAResSyntaxOK'           => 'False',
                    'Expiry'                  => '****',
                    'Pan'                     => '540061******0430',
                    'OrderId'                 => '20221225B83B',
                    'PurchAmount'             => '1,01',
                    'Exponent'                => '',
                    'Description'             => '',
                    'Description2'            => '',
                    'Currency'                => '949',
                    'OkUrl'                   => 'http:\/\/localhost\/interpos\/3d-pay\/response.php',
                    'FailUrl'                 => 'http:\/\/localhost\/interpos\/3d-pay\/response.php',
                    '3DStatus'                => '0',
                    'AuthCode'                => '',
                    'HostRefNum'              => 'hostid',
                    'TransId'                 => '',
                    'TRXDATE'                 => '',
                    'CardHolderName'          => '',
                    'mdStatus'                => '0',
                    'ProcReturnCode'          => '81',
                    'TxnResult'               => '',
                    'ErrorMessage'            => 'Terminal Aktif Degil',
                    'ErrorCode'               => 'B810002',
                    'Response'                => '',
                    'HASH'                    => 'PvDXe6Puf9W2oZnBZuHVp8oWpyY=',
                    'HASHPARAMS'              => 'Version:PurchAmount:Exponent:Currency:OkUrl:FailUrl:MD:OrderId:ProcReturnCode:Response:mdStatus:',
                    'HASHPARAMSVAL'           => '1,01949http:\/\/localhost\/interpos\/3d-pay\/response.phphttp:\/\/localhost\/interpos\/3d-pay\/response.php20221225B83B810',
                ],
                'expectedData' => [
                    'order_id'             => '20221225B83B',
                    'trans_id'             => null,
                    'auth_code'            => null,
                    'ref_ret_num'          => 'hostid',
                    'proc_return_code'     => '81',
                    'status'               => 'declined',
                    'status_detail'        => 'invalid_credentials',
                    'error_code'           => 'B810002',
                    'error_message'        => 'Terminal Aktif Degil',
                    'transaction_security' => 'MPI fallback',
                    'md_status'            => '0',
                    'masked_number'        => '540061******0430',
                    'month'                => null,
                    'year'                 => null,
                    'amount'               => 1.01,
                    'currency'             => PosInterface::CURRENCY_TRY,
                    'eci'                  => null,
                    'tx_status'            => 'N',
                    'cavv'                 => null,
                    'md_error_message'     => 'Terminal Aktif Degil',
                    'transaction_type'     => 'pay',
                    'payment_model'        => '3d_pay',
                ],
            ],
        ];
    }


    public function threeDHostPaymentDataProvider(): array
    {
        return [
            '3d_auth_fail1' => [
                'order'        => [],
                'txType'       => PosInterface::TX_TYPE_PAY,
                'paymentData'  => [
                    'Version'                 => '',
                    'MerchantID'              => '',
                    'ShopCode'                => '3123',
                    'TxnStat'                 => 'N',
                    'MD'                      => '',
                    'RetCode'                 => '',
                    'RetDet'                  => '',
                    'VenderCode'              => '',
                    'Eci'                     => '',
                    'PayerAuthenticationCode' => '',
                    'PayerTxnId'              => '',
                    'CavvAlg'                 => '',
                    'PAResVerified'           => 'False',
                    'PAResSyntaxOK'           => 'False',
                    'Expiry'                  => '****',
                    'Pan'                     => '540061******0430',
                    'OrderId'                 => '202212256D26',
                    'PurchAmount'             => '1,01',
                    'Exponent'                => '',
                    'Description'             => '',
                    'Description2'            => '',
                    'Currency'                => '949',
                    'OkUrl'                   => 'http:\/\/localhost\/interpos\/3d-host\/response.php',
                    'FailUrl'                 => 'http:\/\/localhost\/interpos\/3d-host\/response.php',
                    '3DStatus'                => '0',
                    'AuthCode'                => '',
                    'HostRefNum'              => 'hostid',
                    'TransId'                 => '',
                    'TRXDATE'                 => '',
                    'CardHolderName'          => '',
                    'mdStatus'                => '0',
                    'ProcReturnCode'          => '81',
                    'TxnResult'               => '',
                    'ErrorMessage'            => 'Terminal Aktif Degil',
                    'ErrorCode'               => 'B810002',
                    'Response'                => '',
                    'HASH'                    => 'hmL3n1OMlNnKM4mjk2BgqfFM0rI=',
                    'HASHPARAMS'              => 'Version:PurchAmount:Exponent:Currency:OkUrl:FailUrl:MD:OrderId:ProcReturnCode:Response:mdStatus:',
                    'HASHPARAMSVAL'           => '1,01949http:\/\/localhost\/interpos\/3d-host\/response.phphttp:\/\/localhost\/interpos\/3d-host\/response.php202212256D26810',
                    '__EVENTTARGET'           => '',
                    '__EVENTARGUMENT'         => '',
                ],
                'expectedData' => [
                    'order_id'             => '202212256D26',
                    'trans_id'             => null,
                    'auth_code'            => null,
                    'ref_ret_num'          => 'hostid',
                    'proc_return_code'     => '81',
                    'status'               => 'declined',
                    'status_detail'        => 'invalid_credentials',
                    'error_code'           => 'B810002',
                    'error_message'        => 'Terminal Aktif Degil',
                    'transaction_security' => 'MPI fallback',
                    'md_status'            => '0',
                    'masked_number'        => '540061******0430',
                    'month'                => null,
                    'year'                 => null,
                    'amount'               => 1.01,
                    'currency'             => PosInterface::CURRENCY_TRY,
                    'eci'                  => null,
                    'tx_status'            => 'N',
                    'cavv'                 => null,
                    'md_error_message'     => 'Terminal Aktif Degil',
                    'transaction_type'     => 'pay',
                    'payment_model'        => '3d_host',
                ],
            ],
        ];
    }


    public function statusTestDataProvider(): array
    {
        return
            [
                'fail1' => [
                    'responseData' => [
                        'OrderId'        => 'SYSOID121327781',
                        'ProcReturnCode' => '81',
                        'BatchNo'        => '',
                        'TransId'        => '',
                        'TRXDATE'        => '',
                        'TxnStat'        => '',
                        'PurchAmount'    => '0',
                        'VoidDate'       => '1.1.0001 00:00:00',
                        'TxnStatus'      => '',
                        'ChargeTypeCd'   => '',
                        'ErrorCode'      => 'B810002',
                        'ErrorMessage'   => 'TR:Terminal Aktif Degil',
                        'RefundedAmount' => '0',
                        'AuthCode'       => '',
                    ],
                    'expectedData' => [
                        'order_id'         => 'SYSOID121327781',
                        'proc_return_code' => '81',
                        'trans_id'         => null,
                        'error_message'    => 'TR:Terminal Aktif Degil',
                        'ref_ret_num'      => null,
                        'order_status'     => null,
                        'refund_amount'    => 0.0,
                        'capture_amount'   => null,
                        'status'           => 'declined',
                        'status_detail'    => 'invalid_credentials',
                        'capture'          => null,
                    ],
                ],
            ];
    }

    public function cancelTestDataProvider(): array
    {
        return
            [
                'fail1' => [
                    'responseData' => [
                        'OrderId'               => 'SYSOID121330755',
                        'ProcReturnCode'        => '81',
                        'HostRefNum'            => 'hostid',
                        'AuthCode'              => '',
                        'TxnResult'             => 'Failed',
                        'ErrorMessage'          => 'Terminal Aktif Degil',
                        'CampanyId'             => '',
                        'CampanyInstallCount'   => '0',
                        'CampanyShiftDateCount' => '0',
                        'CampanyTxnId'          => '',
                        'CampanyType'           => '',
                        'CampanyInstallment'    => '0',
                        'CampanyDate'           => '0',
                        'CampanyAmnt'           => '0',
                        'TRXDATE'               => '',
                        'TransId'               => '',
                        'ErrorCode'             => 'B810002',
                        'EarnedBonus'           => '0',
                        'UsedBonus'             => '0',
                        'AvailableBonus'        => '0',
                        'BonusToBonus'          => '0',
                        'CampaignBonus'         => '0',
                        'FoldedBonus'           => '0',
                        'SurchargeAmount'       => '0',
                        'Amount'                => '0',
                        'CardHolderName'        => '',
                    ],
                    'expectedData' => [
                        'order_id'         => 'SYSOID121330755',
                        'group_id'         => null,
                        'auth_code'        => null,
                        'ref_ret_num'      => 'hostid',
                        'proc_return_code' => '81',
                        'trans_id'         => null,
                        'error_code'       => 'B810002',
                        'error_message'    => 'Terminal Aktif Degil',
                        'status'           => 'declined',
                        'status_detail'    => 'invalid_credentials',
                    ],
                ],
            ];
    }

    public function refundTestDataProvider(): array
    {
        return
            [
                'fail1' => [
                    'responseData' => [
                        'OrderId'               => 'SYSOID121332551',
                        'ProcReturnCode'        => '81',
                        'HostRefNum'            => 'hostid',
                        'AuthCode'              => '',
                        'TxnResult'             => 'Failed',
                        'ErrorMessage'          => 'Terminal Aktif Degil',
                        'CampanyId'             => '',
                        'CampanyInstallCount'   => '0',
                        'CampanyShiftDateCount' => '0',
                        'CampanyTxnId'          => '',
                        'CampanyType'           => '',
                        'CampanyInstallment'    => '0',
                        'CampanyDate'           => '0',
                        'CampanyAmnt'           => '0',
                        'TRXDATE'               => '',
                        'TransId'               => '',
                        'ErrorCode'             => 'B810002',
                        'EarnedBonus'           => '0',
                        'UsedBonus'             => '0',
                        'AvailableBonus'        => '0',
                        'BonusToBonus'          => '0',
                        'CampaignBonus'         => '0',
                        'FoldedBonus'           => '0',
                        'SurchargeAmount'       => '0',
                        'Amount'                => '1,01',
                        'CardHolderName'        => '',
                    ],
                    'expectedData' => [
                        'order_id'         => 'SYSOID121332551',
                        'group_id'         => null,
                        'auth_code'        => null,
                        'ref_ret_num'      => 'hostid',
                        'proc_return_code' => '81',
                        'trans_id'         => null,
                        'error_code'       => 'B810002',
                        'error_message'    => 'Terminal Aktif Degil',
                        'status'           => 'declined',
                        'status_detail'    => 'invalid_credentials',
                    ],
                ],
            ];
    }
}