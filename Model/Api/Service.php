<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Api;

use GLSCroatia\Shipping\Model\Api\Client\Request;
use GLSCroatia\Shipping\Model\Api\Client\Response;

class Service
{
    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \GLSCroatia\Shipping\Model\Api\Client
     */
    protected \GLSCroatia\Shipping\Model\Api\Client $client;

    /**
     * @var \GLSCroatia\Shipping\Model\Api\Client\RequestFactory
     */
    protected \GLSCroatia\Shipping\Model\Api\Client\RequestFactory $requestFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $json;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected \Magento\Framework\Event\ManagerInterface $eventManager;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\Model\Api\Client $client
     * @param \GLSCroatia\Shipping\Model\Api\Client\RequestFactory $requestFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\Model\Api\Client $client,
        \GLSCroatia\Shipping\Model\Api\Client\RequestFactory $requestFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->config = $config;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->json = $json;
        $this->eventManager = $eventManager;
    }

    /**
     * Validates parcel data for labels and adds valid parcel data to database.
     *
     * @param array $params
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareLabels(array $params): Response
    {
        /** @var \GLSCroatia\Shipping\Model\Api\Client\Request $request */
        $request = $this->requestFactory->create();
        $request->setMethod('POST');
        $request->setUri($this->getUrl('ParcelService', 'PrepareLabels'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        $params = $this->prepareParams($params);
        $request->setParams($this->json->serialize($params));

        return $this->makeRequest($request);
    }

    /**
     * Generates parcel numbers and PDF document contains labels in byte array format.
     *
     * @param array $params
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPrintedLabels(array $params): Response
    {
        /** @var \GLSCroatia\Shipping\Model\Api\Client\Request $request */
        $request = $this->requestFactory->create();
        $request->setMethod('POST');
        $request->setUri($this->getUrl('ParcelService', 'GetPrintedLabels'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        $params = $this->prepareParams($params);
        $request->setParams($this->json->serialize($params));

        return $this->makeRequest($request);
    }

    /**
     * Calls both PrepareLabels and GetPrintedLabels in one step.
     *
     * @param array $params
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function printLabels(array $params): Response
    {
        /** @var \GLSCroatia\Shipping\Model\Api\Client\Request $request */
        $request = $this->requestFactory->create();
        $request->setMethod('POST');
        $request->setUri($this->getUrl('ParcelService', 'PrintLabels'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        $params = $this->prepareParams($params);
        $request->setParams($this->json->serialize($params));

        return $this->makeRequest($request);
    }

    /**
     * Set DELETED state for labels/parcels with specific database record ID.
     *
     * @param array $params
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteLabels(array $params): Response
    {
        /** @var \GLSCroatia\Shipping\Model\Api\Client\Request $request */
        $request = $this->requestFactory->create();
        $request->setMethod('POST');
        $request->setUri($this->getUrl('ParcelService', 'DeleteLabels'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        $params = $this->prepareParams($params);
        $request->setParams($this->json->serialize($params));

        return $this->makeRequest($request);
    }

    /**
     * Changes COD amount for specific parcel.
     *
     * @param array $params
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyCOD(array $params): Response
    {
        /** @var \GLSCroatia\Shipping\Model\Api\Client\Request $request */
        $request = $this->requestFactory->create();
        $request->setMethod('POST');
        $request->setUri($this->getUrl('ParcelService', 'ModifyCOD'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        $params = $this->prepareParams($params);
        $request->setParams($this->json->serialize($params));

        return $this->makeRequest($request);
    }

    /**
     * Get parcel(s) information by date ranges.
     *
     * @param array $params
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getParcelList(array $params): Response
    {
        /** @var \GLSCroatia\Shipping\Model\Api\Client\Request $request */
        $request = $this->requestFactory->create();
        $request->setMethod('POST');
        $request->setUri($this->getUrl('ParcelService', 'GetParcelList'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        $params = $this->prepareParams($params);
        $request->setParams($this->json->serialize($params));

        return $this->makeRequest($request);
    }

    /**
     * Get parcel statuses with POD.
     *
     * @param array $params
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getParcelStatuses(array $params): Response
    {
        /** @var \GLSCroatia\Shipping\Model\Api\Client\Request $request */
        $request = $this->requestFactory->create();
        $request->setMethod('POST');
        $request->setUri($this->getUrl('ParcelService', 'GetParcelStatuses'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        $params = $this->prepareParams($params);
        $request->setParams($this->json->serialize($params));

        return $this->makeRequest($request);
    }

    /**
     * Generate API URL.
     *
     * @param string $serviceName
     * @param string $methodName
     * @param string $format (json|xml)
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getUrl(string $serviceName, string $methodName, string $format = 'json'): string
    {
        if ($url = $this->config->getApiUrl($serviceName)) {
            return "{$url}{$format}/{$methodName}";
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('API URL is not configured.'));
    }

    /**
     * Get API username.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getUsername(): string
    {
        if ($username = $this->config->getApiUsername()) {
            return $username;
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('API username is not configured.'));
    }

    /**
     * Get API password.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPassword(): array
    {
        if ($password = $this->config->getApiPassword()) {
            $passwordData = unpack('C*', hash('sha512', $password, true)) ?: []; // phpcs:ignore
            return array_values($passwordData);
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('API password is not configured.'));
    }

    /**
     * @param array $params
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareParams(array $params): array
    {
        if (!isset($params['Username'])) {
            $params['Username'] = $this->getUsername();
        }
        if (!isset($params['Password'])) {
            $params['Password'] = $this->getPassword();
        }
        if (!isset($params['WebshopEngine'])) {
            $params['WebshopEngine'] = 'magentohr';
        }

        return $params;
    }

    /**
     * Make an API request.
     *
     * @param \GLSCroatia\Shipping\Model\Api\Client\Request $request
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function makeRequest(Request $request): Response
    {
        $this->eventManager->dispatch('gls_before_api_request', [
            'request' => $request,
            'object'  => $request
        ]);

        if (strtolower($request->getMethod()) === 'get') {
            $response = $this->client->get($request);
        } else {
            $response = $this->client->post($request);
        }

        $this->eventManager->dispatch('gls_after_api_request', [
            'response' => $response,
            'object'   => $response
        ]);

        return $response;
    }
}
