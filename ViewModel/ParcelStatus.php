<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\ViewModel;

class ParcelStatus implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected \Magento\Framework\UrlInterface $urlBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected \Magento\Framework\Registry $registry;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected \Magento\Framework\Url\EncoderInterface $urlEncoder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface
     */
    protected \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter;

    /**
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->registry = $registry;
        $this->urlEncoder = $urlEncoder;
        $this->localeDate = $localeDate;
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    /**
     * Get GLS parcel status popup URL.
     *
     * @param \Magento\Sales\Model\Order\Shipment|\Magento\Sales\Model\Order $source
     * @param string $hashKey
     * @return string
     */
    public function getGlsStatusPopupUrl(\Magento\Sales\Model\AbstractModel $source, string $hashKey): string
    {
        $urlPart = "{$hashKey}:{$source->getId()}:{$source->getProtectCode()}";

        $params = [
            '_scope' => $source->getStoreId(),
            '_nosid' => true,
            '_direct' => 'gls/parcelStatus/popup',
            '_query' => ['hash' => $this->urlEncoder->encode($urlPart)]
        ];

        return $this->urlBuilder->getUrl('', $params);
    }

    /**
     * Get current GLS parcel statuses.
     *
     * @return \Magento\Framework\DataObject|null
     */
    public function getParcelListStatuses(): ?\Magento\Framework\DataObject
    {
        return $this->registry->registry('parcel_list_statuses');
    }

    /**
     * Format GLS API date.
     *
     * @param string $date
     * @return string
     */
    public function formatDate(string $date): string
    {
        preg_match('/\/Date\((\d+)([+-]\d{4})?\)\//', $date, $matches); // extract timestamp (milliseconds)
        $timestamp = (int)($matches[1] / 100);
        $dateTime = (new \DateTime())->setTimestamp($timestamp);

        try {
            return $this->dateTimeFormatter->formatObject(
                $this->localeDate->date($dateTime),
                $this->localeDate->getDateTimeFormat(\IntlDateFormatter::SHORT)
            );
        } catch (\Exception $e) {
            return '';
        }
    }
}
