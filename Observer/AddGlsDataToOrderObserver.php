<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddGlsDataToOrderObserver implements ObserverInterface
{
    /**
     * Save GLS delivery location to the order.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getData('quote');

        $order->setData('gls_data', $quote->getShippingAddress()->getData('gls_data'));
    }
}
