<?php
/**
 * @license MIT
 */

namespace Mews\Pos\Crypt;

use Mews\Pos\Entity\Account\AbstractPosAccount;
use Mews\Pos\Exceptions\NotImplementedException;

class EstV3PosCrypt extends AbstractCrypt
{
    /** @var string */
    protected const HASH_ALGORITHM = 'sha512';

    /** @var string */
    protected const HASH_SEPARATOR = '|';

    /**
     * {@inheritDoc}
     */
    public function create3DHash(AbstractPosAccount $account, array $requestData): string
    {
        \ksort($requestData, SORT_NATURAL | SORT_FLAG_CASE);
        foreach (\array_keys($requestData) as $key) {
            /**
             * this part is needed only to create hash from the bank response
             *
             * nationalidno: Ziraat ödeme dönüşlerinde checkHash arrayi içerisinde yer alabiliyor.
             *  Hash string içine dahil edildiğinde hataya sebep oluyor,
             *  Payten tarafından hash içerisinde olmaması gerektiği teyidi alındı.
             */
            if (\in_array(\strtolower($key), ['hash', 'encoding' , 'nationalidno']))  {
                unset($requestData[$key]);
            }
        }

        $requestData[] = $account->getStoreKey();
        // escape | and \ characters
        $data = \str_replace("\\", "\\\\", \array_values($requestData));
        $data = \str_replace(self::HASH_SEPARATOR, "\\".self::HASH_SEPARATOR, $data);

        $hashStr = \implode(self::HASH_SEPARATOR, $data);

        return $this->hashString($hashStr);
    }

    /**
     * {@inheritdoc}
     */
    public function check3DHash(AbstractPosAccount $account, array $data): bool
    {
        $actualHash = $this->create3DHash($account, $data);

        if ($data['HASH'] === $actualHash) {
            $this->logger->debug('hash check is successful');

            return true;
        }

        $this->logger->error('hash check failed', [
            'data'           => $data,
            'generated_hash' => $actualHash,
            'expected_hash'  => $data['HASH'],
        ]);

        return false;
    }

    /**
     * @param AbstractPosAccount   $account
     * @param array<string, mixed> $requestData
     *
     * @return string
     */
    public function createHash(AbstractPosAccount $account, array $requestData): string
    {
        throw new NotImplementedException();
    }
}
