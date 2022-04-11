<?php

namespace Mews\Pos\Entity\Card;

/**
 * Class CreditCardEstPos
 */
class CreditCardInterPos extends AbstractCreditCard
{
    private $cardTypeToCodeMapping = [
        'visa'   => '0',
        'master' => '1',
        'amex'   => '3',
    ];

    /**
     * @inheritDoc
     */
    public function getExpirationDate(): string
    {
        return $this->getExpireMonth().$this->getExpireYear();
    }

    /**
     * @return string
     */
    public function getCardCode(): string
    {
        if (!isset($this->cardTypeToCodeMapping[$this->type])) {
            return $this->type;
        }

        return $this->cardTypeToCodeMapping[$this->type];
    }

    /**
     * @return string[]
     */
    public function getCardTypeToCodeMapping(): array
    {
        return $this->cardTypeToCodeMapping;
    }
}
