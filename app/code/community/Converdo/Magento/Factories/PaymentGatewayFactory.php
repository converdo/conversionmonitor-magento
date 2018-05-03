<?php

namespace Converdo\ConversionMonitor\Magento\Factories;

use Converdo\ConversionMonitor\Core\Factories\BasePaymentGatewayFactory;
use Mage;
use Mage_Payment_Model_Method_Abstract;

class PaymentGatewayFactory extends BasePaymentGatewayFactory
{
    /**
     * The payment gateway instance.
     *
     * @var Mage_Payment_Model_Method_Abstract
     */
    protected $gateway;

    /**
     * PaymentGatewayFactory constructor.
     *
     * @param  Mage_Payment_Model_Method_Abstract   $gateway
     */
    public function __construct(Mage_Payment_Model_Method_Abstract $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        return $this->model
                    ->setIdentifier($this->gateway->getCode())
                    ->setName(Mage::getStoreConfig("payment/{$this->gateway->getCode()}/title"))
                    ->setMethod(Mage::getStoreConfig("payment/{$this->gateway->getCode()}/group"))
                    ->setIsEnabled(Mage::getStoreConfig("payment/{$this->gateway->getCode()}/active"));
    }
}