<?php

require_once dirname(dirname(__DIR__)) . '/Core/bootstrap.php';

use Converdo\ConversionMonitor\Core\API\API;

class Converdo_Magento_Model_Observer
{
    /**
     * Listens to placed orders. Sends the order to the conversion monitor api.
     *
     * @param  Varien_Event_Observer        $observer
     * @return void
     */
    public function onOrderPlacedEvent(Varien_Event_Observer $observer)
    {
        if (cvd_config()->platform()->disabled() || ! $observer->getEvent()->getOrder()) {
            return;
        }

        cvd_app()->make(API::class)->order_create(
            cvd_config()->platform()->order($observer->getEvent()->getOrder())
        );
    }

    /**
     * Listens to changed orders. Sends the new order status to the conversion monitor api.
     *
     * @param  Varien_Event_Observer        $observer
     * @return void
     */
    public function onOrderStatusChangedEvent(Varien_Event_Observer $observer)
    {
        if (cvd_config()->platform()->disabled() || ! $observer->getEvent()->getOrder()) {
            return;
        }

        cvd_app()->make(API::class)->order_update(
            cvd_config()->platform()->order($observer->getEvent()->getOrder())
        );
    }
}
