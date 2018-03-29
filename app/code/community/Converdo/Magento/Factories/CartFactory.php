<?php

namespace Converdo\ConversionMonitor\Magento\Factories;

use Converdo\ConversionMonitor\Core\Factories\BaseCartFactory;
use Converdo\ConversionMonitor\Core\Trackables\TrackableCoupon;
use Converdo\ConversionMonitor\Core\Trackables\TrackableProduct;
use Mage_Sales_Model_Quote;

class CartFactory extends BaseCartFactory
{
    /**
     * The cart instance.
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $cart;

    /**
     * CartFactory constructor.
     *
     * @param  Mage_Sales_Model_Quote   $cart
     */
    public function __construct(Mage_Sales_Model_Quote $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        return $this->model
                    ->setProducts($this->handleProducts())
                    ->setTotal($this->cart->getGrandTotal())
                    ->setSubtotal($this->cart->getSubtotal())
                    ->setShipping($this->cart->getShippingAddress()->getData('shipping_amount'))
                    ->setDiscount($this->handleDiscount())
                    ->setTax($this->handleTax())
                    ->setCoupons($this->handleCoupons());
    }

    /**
     * Handle the cart product instances.
     *
     * @return TrackableProduct[]
     */
    protected function handleProducts()
    {
        $products = [];

        foreach ($this->cart->getAllVisibleItems() as $product) {
            $products[] = (new ProductFactory($product->getProduct(), $product->getQty()))->call();
        }

        return $products;
    }

    /**
     * Get the tax applied to the cart.
     *
     * @return float
     */
    protected function handleTax()
    {
        return $this->cart->getShippingAddress()->getData('tax_amount') ?: $this->cart->getCustomerTaxvat();
    }

    /**
     * Get the discount applied to the cart.
     *
     * @return float
     */
    protected function handleDiscount()
    {
        return $this->cart->getSubtotal() - $this->cart->getSubtotalWithDiscount();
    }

    /**
     * Handle the cart coupon instances.
     *
     * @return TrackableCoupon[]
     */
    protected function handleCoupons()
    {
        if (! $this->cart->getCouponCode()) {
            return [];
        }

        $coupon = new \Mage_SalesRule_Model_Coupon();

        return [cvd_config()->platform()->coupon($this->cart, $coupon->loadByCode($this->cart->getCouponCode()))];
    }
}