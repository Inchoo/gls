<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Api;

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
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\Model\Api\Client $client
     * @param \GLSCroatia\Shipping\Model\Api\Client\RequestFactory $requestFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\Model\Api\Client $client,
        \GLSCroatia\Shipping\Model\Api\Client\RequestFactory $requestFactory,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->config = $config;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->json = $json;
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
        $request->setUri($this->getUrl('ParcelService', 'PrepareLabels'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        if (!isset($params['Username'])) {
            $params['Username'] = $this->getUsername();
        }
        if (!isset($params['Password'])) {
            $params['Password'] = $this->getPassword();
        }

        $request->setParams($this->json->serialize($params));

        return $this->client->post($request);
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
        $request->setUri($this->getUrl('ParcelService', 'GetPrintedLabels'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        if (!isset($params['Username'])) {
            $params['Username'] = $this->getUsername();
        }
        if (!isset($params['Password'])) {
            $params['Password'] = $this->getPassword();
        }

        $request->setParams($this->json->serialize($params));

        return $this->client->post($request);
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
        $request->setUri($this->getUrl('ParcelService', 'PrintLabels'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        if (!isset($params['Username'])) {
            $params['Username'] = $this->getUsername();
        }
        if (!isset($params['Password'])) {
            $params['Password'] = $this->getPassword();
        }

        $request->setParams($this->json->serialize($params));

        return $this->client->post($request);
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
        $request->setUri($this->getUrl('ParcelService', 'DeleteLabels'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        if (!isset($params['Username'])) {
            $params['Username'] = $this->getUsername();
        }
        if (!isset($params['Password'])) {
            $params['Password'] = $this->getPassword();
        }

        $request->setParams($this->json->serialize($params));

        return $this->client->post($request);
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
        $request->setUri($this->getUrl('ParcelService', 'ModifyCOD'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        if (!isset($params['Username'])) {
            $params['Username'] = $this->getUsername();
        }
        if (!isset($params['Password'])) {
            $params['Password'] = $this->getPassword();
        }

        $request->setParams($this->json->serialize($params));

        return $this->client->post($request);
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
        $request->setUri($this->getUrl('ParcelService', 'GetParcelList'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        if (!isset($params['Username'])) {
            $params['Username'] = $this->getUsername();
        }
        if (!isset($params['Password'])) {
            $params['Password'] = $this->getPassword();
        }

        $request->setParams($this->json->serialize($params));

        return $this->client->post($request);
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
        $request->setUri($this->getUrl('ParcelService', 'GetParcelStatuses'));
        $request->setHeaders(['Content-Type' => 'application/json']);

        if (!isset($params['Username'])) {
            $params['Username'] = $this->getUsername();
        }
        if (!isset($params['Password'])) {
            $params['Password'] = $this->getPassword();
        }

        $request->setParams($this->json->serialize($params));

        return $this->client->post($request);
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
}
