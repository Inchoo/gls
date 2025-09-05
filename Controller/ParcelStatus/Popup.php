<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Controller\ParcelStatus;

class Popup extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \GLSCroatia\Shipping\Model\Api\Service
     */
    protected \GLSCroatia\Shipping\Model\Api\Service $apiService;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository;

    /**ž
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected \Magento\Framework\Registry $registry;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected \Magento\Framework\Url\DecoderInterface $urlDecoder;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\Model\Api\Service $apiService
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\Model\Api\Service $apiService,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->config = $config;
        $this->apiService = $apiService;
        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->storeManager = $storeManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        $this->urlDecoder = $urlDecoder;
        parent::__construct($context);
    }

    /**
     * GLS parcel status popup.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $hash = $this->getRequest()->getParam('hash');
        if (!$hash || !$parcelNumbers = $this->loadParcelNumbers((string)$hash)) {
            $resultForward = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);
            return $resultForward->forward('noroute');
        }

        try {
            $apiCountryCode = $this->config->getApiCountryCode($this->storeManager->getStore()->getId());
            $allowedLanguageCode = ['EN', 'HR', 'CS', 'HU', 'RO', 'SK', 'SL'];
            $languageCode = in_array($apiCountryCode, $allowedLanguageCode, true) ? $apiCountryCode : 'EN';

            $response = $this->apiService->getParcelListStatuses([
                'ParcelNumberList' => $parcelNumbers,
                'LanguageIsoCode' => $languageCode,
                'ReturnPOD' => false
            ]);

            $responseBody = $response->getDecodedBody();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseBody = [];
        }

        $result = $this->dataObjectFactory->create();
        $result->setData('parcel_list_statuses_errors', $responseBody['GetParcelListStatusesErrors'] ?? []);
        $result->setData('parcel_list', $responseBody['ParcelList'] ?? []);

        $this->registry->register('parcel_list_statuses', $result);

        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('GLS Parcel Status'));
        return $resultPage;
    }

    /**
     * Load GLS parcel numbers from "hash" query string.
     *
     * @param string $hash
     * @return array
     */
    protected function loadParcelNumbers(string $hash): array
    {
        $hashData = explode(':', $this->urlDecoder->decode($hash));
        if (count($hashData) !== 3) {
            return [];
        }

        /** @var \Magento\Sales\Model\Order\Shipment[] $shipments */
        $shipments = [];

        if ($hashData[0] === 'shipment_id') {
            try {
                $shipment = $this->shipmentRepository->get((int)$hashData[1]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                return [];
            }

            if ($hashData[2] !== $shipment->getProtectCode()) {
                return [];
            }

            $shipments[] = $shipment;
        } elseif ($hashData[0] === 'order_id') {
            try {
                $order = $this->orderRepository->get((int)$hashData[1]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                return [];
            }

            if ($hashData[2] !== $order->getProtectCode()) {
                return [];
            }

            $shipments = $order->getShipmentsCollection()->getItems();
        }

        $parcelNumbers = [];
        foreach ($shipments as $shipment) {
            foreach ($shipment->getTracks() as $track) {
                if ($track->getCarrierCode() === \GLSCroatia\Shipping\Model\Carrier::CODE) {
                    $parcelNumbers[] = $track->getTrackNumber();
                }
            }
        }

        return $parcelNumbers;
    }
}
