<?php

namespace Converdo\ConversionMonitor\Magento\Factories;

use Converdo\ConversionMonitor\Core\Enumerables\Orders\CancelledType;
use Converdo\ConversionMonitor\Core\Enumerables\Orders\CompletedType;
use Converdo\ConversionMonitor\Core\Enumerables\Orders\PendingType;
use Converdo\ConversionMonitor\Core\Enumerables\OrderType;
use Converdo\ConversionMonitor\Core\Factories\BaseOrderFactory;
use Converdo\ConversionMonitor\Core\Trackables\TrackableAddress;
use Converdo\ConversionMonitor\Core\Trackables\TrackableCustomer;
use Mage_Sales_Model_Order;

class OrderFactory extends BaseOrderFactory
{
    /**
     * The order instance.
     *
     * @var Mage_Sales_Model_Order
     */
    protected $order;

    /**
     * CategoryFactory constructor.
     *
     * @param  Mage_Sales_Model_Order   $order
     */
    public function __construct(Mage_Sales_Model_Order $order)
    {
        $this->order = $order;
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        return $this->model
                    ->setIdentifier($this->order->getRealOrderId())
                    ->setSubtotal($this->order->getSubtotal())
                    ->setTotal($this->order->getGrandTotal())
                    ->setShipping($this->order->getBaseShippingAmount())
                    ->setDiscount($this->order->getBaseDiscountAmount())
                    ->setTax($this->order->getBaseTaxAmount())
                    ->setGateway($this->handlePaymentGateway())
                    ->setCustomer($this->handleCustomer())
                    ->setType($this->handleOrderType())
                    ->setProducts($this->handleProducts());
    }

    /**
     * Get the order status type.
     *
     * @return OrderType
     */
    protected function handleOrderType()
    {
        switch (strtolower($this->order->getStatus())) {
            case 'payment_review':
            case 'pending_payment':
            case 'new':
            case 'holded':
                return new PendingType();

            case 'canceled':
            case 'closed':
            return new CancelledType();

            default:
                return new CompletedType();
        }
    }

    /**
     * Get the order products.
     *
     * @return array
     */
    protected function handleProducts()
    {
        $products = [];

        foreach ($this->order->getAllVisibleItems() as $product) {
            $products[] = cvd_config()->platform()->getProductFactory(
                $product->getProduct(), (int) $product->getQtyOrdered()
            )->call();
        }

        return $products;
    }

    /**
     * Get the payment gateway name.
     *
     * @return string|null
     */
    protected function handlePaymentGateway()
    {
        if ($this->order->getPayment() && $this->order->getPayment()->getMethodInstance()) {
            return $this->order->getPayment()->getMethodInstance()->getCode();
        }

        return null;
    }

    /**
     * Build the customer trackable instance.
     *
     * @return TrackableCustomer
     */
    protected function handleCustomer()
    {
        $billing = new TrackableAddress();
        $billing->setAddress($this->order->getBillingAddress()->getStreet1());
        $billing->setPostal($this->order->getBillingAddress()->getPostcode());
        $billing->setCity($this->order->getBillingAddress()->getCity());
        $billing->setCountry($this->order->getBillingAddress()->getCountryModel()->getName());

        $shipping = new TrackableAddress();
        $shipping->setAddress($this->order->getShippingAddress()->getStreet1());
        $shipping->setPostal($this->order->getShippingAddress()->getPostcode());
        $shipping->setCity($this->order->getShippingAddress()->getCity());
        $shipping->setCountry($this->order->getShippingAddress()->getCountryModel()->getName());

        $customer = new TrackableCustomer();
        $customer->setName($this->order->getCustomerName());
        $customer->setEmail($this->order->getCustomerEmail());
        $customer->setTelephone($this->order->getShippingAddress()->getTelephone());
        $customer->setBillingAddress($billing);
        $customer->setShippingAddress($shipping);

        return $customer;
    }
}