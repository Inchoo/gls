<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Api;

use GLSCroatia\Shipping\Model\Api\Client\Request;
use GLSCroatia\Shipping\Model\Api\Client\Response;
use Magento\Framework\HTTP\Client\Curl;

class Client
{
    /**
     * @var \GLSCroatia\Shipping\Model\Api\Client\ResponseFactory
     */
    protected \GLSCroatia\Shipping\Model\Api\Client\ResponseFactory $responseFactory;

    /**
     * @var \Magento\Framework\HTTP\Client\CurlFactory
     */
    protected \Magento\Framework\HTTP\Client\CurlFactory $curlFactory;

    /**
     * @param \GLSCroatia\Shipping\Model\Api\Client\ResponseFactory $responseFactory
     * @param \Magento\Framework\HTTP\Client\CurlFactory $curlFactory
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Api\Client\ResponseFactory $responseFactory,
        \Magento\Framework\HTTP\Client\CurlFactory $curlFactory
    ) {
        $this->responseFactory = $responseFactory;
        $this->curlFactory = $curlFactory;
    }

    /**
     * Make a POST request.
     *
     * @param \GLSCroatia\Shipping\Model\Api\Client\Request $request
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function post(Request $request): Response
    {
        $curl = $this->createCurlClient($request);

        try {
            $curl->post($request->getUri(), $request->getParams() ?: []);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }

        return $this->createResponse($curl);
    }

    /**
     * Make a GET request.
     *
     * @param \GLSCroatia\Shipping\Model\Api\Client\Request $request
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get(Request $request): Response
    {
        $curl = $this->createCurlClient($request);

        try {
            $curl->get($request->getUri());
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }

        return $this->createResponse($curl);
    }

    /**
     * Create a cURL client object.
     *
     * @param \GLSCroatia\Shipping\Model\Api\Client\Request $request
     * @return \Magento\Framework\HTTP\Client\Curl
     */
    protected function createCurlClient(Request $request): Curl
    {
        $curl = $this->curlFactory->create();

        if ($headers = $request->getHeaders()) {
            $curl->setHeaders($headers);
        }
        if ($cookies = $request->getCookies()) {
            $curl->setCookies($cookies);
        }
        if ($curlOptions = $request->getOptions()) {
            $curl->setOptions($curlOptions);
        }
        $timeout = $request->getTimeout();
        if ($timeout !== null) {
            $curl->setTimeout($timeout);
        }

        return $curl;
    }

    /**
     * Create an API response object.
     *
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @return \GLSCroatia\Shipping\Model\Api\Client\Response
     */
    protected function createResponse(Curl $curl): Response
    {
        /** @var \GLSCroatia\Shipping\Model\Api\Client\Response $response */
        $response = $this->responseFactory->create();
        $response->setHeaders($curl->getHeaders());
        $response->setBody($curl->getBody());
        $response->setCookies($curl->getCookies());
        $response->setStatus((int)$curl->getStatus());

        return $response;
    }
}
