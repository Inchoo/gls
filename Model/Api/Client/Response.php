<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Api\Client;

use Magento\Framework\DataObject;

/**
 * @method \GLSCroatia\Shipping\Model\Api\Client\Response setHeaders(array $headers)
 * @method array|null getHeaders()
 *
 * @method \GLSCroatia\Shipping\Model\Api\Client\Response setBody(string $body)
 * @method string|null getBody()
 *
 * @method \GLSCroatia\Shipping\Model\Api\Client\Response setCookies(array $cookies)
 * @method array|null getCookies()
 *
 * @method \GLSCroatia\Shipping\Model\Api\Client\Response setStatus(int $body)
 * @method int|null getStatus()
 */
class Response extends DataObject
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $json;

    /**
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        $this->json = $json;
        parent::__construct($data);
    }

    /**
     * Decode JSON body.
     *
     * @return array
     */
    public function getDecodedBody(): array
    {
        try {
            return $this->getBody() ? $this->json->unserialize($this->getBody()) : [];
        } catch (\InvalidArgumentException $e) {
            return [];
        }
    }
}
