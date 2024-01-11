<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ApiLogObserver implements ObserverInterface
{
    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $json;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected \Psr\Log\LoggerInterface $logger;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * Log GLS API requests/response.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isDebugEnabled()) {
            return;
        }

        /** @var \GLSCroatia\Shipping\Model\Api\Client\Request $request */
        if ($request = $observer->getData('request')) {
            $this->logger->debug('GLS API request', $this->sanitizeRequestData($request));
        }

        /** @var \GLSCroatia\Shipping\Model\Api\Client\Response $response */
        if ($response = $observer->getData('response')) {
            $this->logger->debug('GLS API response', $this->sanitizeResponseData($response));
        }
    }

    /**
     * Sanitize request data.
     *
     * @param \GLSCroatia\Shipping\Model\Api\Client\Request $request
     * @return array
     */
    public function sanitizeRequestData(DataObject $request): array
    {
        $data = $request->getData() ?: [];
        $params = $data['params'] ?? [];

        if (is_string($params)) {
            try {
                $params = $this->json->unserialize($params);
            } catch (\InvalidArgumentException $e) {
                $params = [];
            }
        }

        if (isset($params['Password'])) {
            $params['Password'] = 'SANITIZED';
        }

        $data['params'] = $params;
        return $data;
    }

    /**
     * Sanitize response data.
     *
     * @param \GLSCroatia\Shipping\Model\Api\Client\Response  $response
     * @return array
     */
    public function sanitizeResponseData(DataObject $response): array
    {
        $body = $response->getDecodedBody();

        if (isset($body['Labels']) && $body['Labels']) {
            $body['Labels'] = 'SANITIZED';
        }
        if (isset($body['POD']) && $body['POD']) {
            $body['POD'] = 'SANITIZED';
        }

        return [
            'statusCode' => $response->getStatus(),
            'body' => $body
        ];
    }
}
