<?php
/**
 * @license MIT
 */

namespace Mews\Pos\Serializer;

use DomainException;
use Exception;
use Mews\Pos\Gateways\PayFlexCPV4Pos;
use Mews\Pos\PosInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Serializer;
use function sprintf;
use function strip_tags;

class PayFlexCPV4PosSerializer implements SerializerInterface
{
    /** @var Serializer */
    private $serializer;

    public function __construct()
    {
        $encoder = new XmlEncoder([
            XmlEncoder::ROOT_NODE_NAME             => 'VposRequest',
            XmlEncoder::ENCODING                   => 'UTF-8',
            XmlEncoder::ENCODER_IGNORED_NODE_TYPES => [
                XML_PI_NODE,
            ],
        ]);

        $this->serializer = new Serializer([], [$encoder]);
    }

    /**
     * @inheritDoc
     */
    public static function supports(string $gatewayClass): bool
    {
        return PayFlexCPV4Pos::class === $gatewayClass;
    }

    /**
     * @inheritDoc
     */
    public function encode(array $data, string $txType): string
    {
        if ($txType === PosInterface::TX_HISTORY || $txType === PosInterface::TX_STATUS) {
            throw new DomainException(sprintf('Serialization of the transaction %s is not supported', $txType));
        }

        return $this->serializer->encode($data, XmlEncoder::FORMAT);
    }

    /**
     * @inheritDoc
     */
    public function decode(string $data, ?string $txType = null): array
    {
        try {
            return $this->serializer->decode($data, XmlEncoder::FORMAT);
        } catch (NotEncodableValueException $notEncodableValueException) {
            if ($this->isHTML($data)) {
                // if something wrong server responds with HTML content
                throw new Exception($data, $notEncodableValueException->getCode(), $notEncodableValueException);
            }
        }

        throw $notEncodableValueException;
    }

    private function isHTML(string $str): bool
    {
        return $str !== strip_tags($str);
    }
}