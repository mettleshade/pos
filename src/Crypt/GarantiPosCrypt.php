<?php
/**
 * @license MIT
 */

namespace Mews\Pos\Crypt;

use Mews\Pos\Entity\Account\AbstractPosAccount;
use Mews\Pos\Entity\Account\GarantiPosAccount;

class GarantiPosCrypt extends AbstractCrypt
{
    /**
     * @param GarantiPosAccount $account
     * {@inheritDoc}
     */
    public function create3DHash(AbstractPosAccount $account, array $requestData): string
    {
        $map = [
            $account->getTerminalId(),
            $requestData['orderid'],
            $requestData['txnamount'],
            $requestData['successurl'],
            $requestData['errorurl'],
            $requestData['txntype'],
            $requestData['txninstallmentcount'],
            $account->getStoreKey(),
            $this->createSecurityData($account, $requestData['txntype']),
        ];

        return $this->hashStringUpperCase(implode(static::HASH_SEPARATOR, $map));
    }

    /**
     * {@inheritdoc}
     */
    public function check3DHash(AbstractPosAccount $account, array $data): bool
    {
        $actualHash = $this->hashFromParams($account->getStoreKey(), $data, 'hashparams', ':');

        if ($data['hash'] === $actualHash) {
            $this->logger->debug('hash check is successful');

            return true;
        }

        $this->logger->error('hash check failed', [
            'data'           => $data,
            'generated_hash' => $actualHash,
            'expected_hash'  => $data['hash'],
        ]);

        return false;
    }

    /**
     * Make Hash Data
     *
     * @param GarantiPosAccount       $account
     * {@inheritDoc}
     */
    public function createHash(AbstractPosAccount $account, array $requestData): string
    {
        $map = [
            $requestData['Order']['OrderID'],
            $account->getTerminalId(),
            $requestData['Card']['Number'] ?? null,
            $requestData['Transaction']['Amount'],
            $this->createSecurityData($account, $requestData['Transaction']['Type']),
        ];

        return $this->hashStringUpperCase(implode(static::HASH_SEPARATOR, $map));
    }

    /**
     * Make Security Data
     *
     * @param GarantiPosAccount $account
     * @param string|null       $txType
     *
     * @return string
     */
    private function createSecurityData(AbstractPosAccount $account, ?string $txType = null): string
    {
        $password = 'void' === $txType || 'refund' === $txType ? $account->getRefundPassword() : $account->getPassword();

        $map = [
            $password,
            str_pad($account->getTerminalId(), 9, '0', STR_PAD_LEFT),
        ];

        return $this->hashStringUpperCase(implode(static::HASH_SEPARATOR, $map));
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private function hashStringUpperCase(string $str): string
    {
        return strtoupper(hash(static::HASH_ALGORITHM, $str));
    }
}
