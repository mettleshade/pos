<?php
/**
 * @license MIT
 */
namespace Mews\Pos\DataMapper\RequestDataMapper;

use Mews\Pos\Entity\Account\AbstractPosAccount;
use Mews\Pos\Entity\Card\AbstractCreditCard;
use Mews\Pos\Event\Before3DFormHashCalculatedEvent;

/**
 * Creates request data for EstPos Gateway requests that supports v3 Hash algorithm
 */
class EstV3PosRequestDataMapper extends EstPosRequestDataMapper
{
    /**
     * {@inheritDoc}
     */
    public function create3DFormData(AbstractPosAccount $account, array $order, string $paymentModel, string $txType, string $gatewayURL, ?AbstractCreditCard $card = null): array
    {
        $order = $this->preparePaymentOrder($order);

        $data = $this->create3DFormDataCommon($account, $order, $paymentModel, $txType, $gatewayURL, $card);

        $data['inputs']['TranType'] = $this->mapTxType($txType);
        unset($data['inputs']['islemtipi']);

        $data['inputs']['hashAlgorithm'] = 'ver3';

        $event = new Before3DFormHashCalculatedEvent($data['inputs'], $account->getBank(), $txType, $paymentModel);
        $this->eventDispatcher->dispatch($event);
        $data['inputs'] = $event->getRequestData();

        $data['inputs']['hash'] = $this->crypt->create3DHash($account, $data['inputs']);

        return $data;
    }
}