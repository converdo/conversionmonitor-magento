<?php

namespace Converdo\ConversionMonitor\Magento\Configuration;

use Converdo\ConversionMonitor\Core\Contracts\PlatformConfigurable;
use Converdo\ConversionMonitor\Core\Factories\BaseVisitorFactory;
use Converdo\ConversionMonitor\Magento\Factories\CartFactory;
use Converdo\ConversionMonitor\Magento\Factories\CategoryFactory;
use Converdo\ConversionMonitor\Magento\Factories\CouponFactory;
use Converdo\ConversionMonitor\Magento\Factories\OrderFactory;
use Converdo\ConversionMonitor\Magento\Factories\PageFactory;
use Converdo\ConversionMonitor\Magento\Factories\PaymentGatewayFactory;
use Converdo\ConversionMonitor\Magento\Factories\ProductFactory;
use Converdo\ConversionMonitor\Magento\Factories\SearchFactory;
use Mage;

class Configuration implements PlatformConfigurable
{
    /**
     * Determine if the plugin is activated.
     *
     * @var bool
     */
    protected $activated = true;

    /**
     * @inheritDoc
     */
    public function enabled($store = null)
    {
        return $this->location($store)
            && $this->website($store)
            && $this->encryption($store)
            && $this->activated($store)
            && $this->activated;
    }

    /**
     * @inheritDoc
     */
    public function disabled($store = null)
    {
        return ! $this->enabled($store);
    }

    /**
     * @inheritDoc
     */
    public function website($store = null)
    {
        return (string) Mage::getStoreConfig('converdo/conversionmonitorapi/website', $store);
    }

    /**
     * @inheritDoc
     */
    public function encryption($store = null)
    {
        return (string) Mage::getStoreConfig('converdo/conversionmonitorapi/encryption', $store);
    }

    /**
     * @inheritDoc
     */
    public function location($store = null)
    {
        return (string) Mage::getStoreConfig('converdo/conversionmonitorapi/cluster', $store);
    }

    /**
     * @inheritDoc
     */
    public function activated($store = null)
    {
        return (bool) Mage::getStoreConfig('converdo/conversionmonitorsettings/active', $store);
    }

    /**
     * @inheritDoc
     */
    public function terminate()
    {
        $this->activated = false;
    }

    /**
     * @inheritdoc
     */
    public function isPage()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function page()
    {
        return $this->getPageFactory(null)->call();
    }

    /**
     * @inheritdoc
     */
    public function getPageFactory($page)
    {
        return new PageFactory($page);
    }

    /**
     * @inheritdoc
     */
    public function isProduct()
    {
        return Mage::app()->getFrontController()->getRequest()->getControllerName() === 'product'
            && Mage::registry('current_product');
    }

    /**
     * @inheritdoc
     */
    public function product()
    {
        return $this->getProductFactory(Mage::registry('current_product'))->call();
    }

    /**
     * @inheritdoc
     */
    public function getProductFactory($product, $quantity = null)
    {
        return new ProductFactory($product, $quantity);
    }

    /**
     * @inheritdoc
     */
    public function isCategory()
    {
        return Mage::app()->getFrontController()->getRequest()->getControllerName() === 'category'
            && Mage::registry('current_category');
    }

    /**
     * @inheritdoc
     */
    public function category()
    {
        return $this->getCategoryFactory(Mage::registry('current_category'))->call();
    }

    /**
     * @inheritdoc
     */
    public function getCategoryFactory($category)
    {
        return new CategoryFactory($category);
    }

    /**
     * @inheritdoc
     */
    public function isSearch()
    {
        return Mage::app()->getFrontController()->getRequest()->getControllerName() === 'result'
            && Mage::helper('catalogsearch');
    }

    /**
     * @inheritdoc
     */
    public function search()
    {
        return $this->getSearchFactory(Mage::helper('catalogsearch')->getQuery())->call();
    }

    /**
     * @inheritdoc
     */
    public function getSearchFactory($search)
    {
        return new SearchFactory($search);
    }

    /**
     * @inheritdoc
     */
    public function visitor()
    {
        return $this->getVisitorFactory()->call();
    }

    /**
     * @inheritdoc
     */
    public function getVisitorFactory()
    {
        return new BaseVisitorFactory();
    }

    /**
     * @inheritdoc
     */
    public function hasCart()
    {
        return (bool) Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();
    }

    /**
     * @inheritdoc
     */
    public function cart()
    {
        return $this->getCartFactory(Mage::getModel('checkout/cart')->getQuote())->call();
    }

    /**
     * @inheritdoc
     */
    public function getCartFactory($cart)
    {
        return new CartFactory($cart);
    }

    /**
     * @inheritdoc
     */
    public function coupon($cart, $coupon)
    {
        return $this->getCouponFactory($cart, $coupon)->call();
    }

    /**
     * @inheritdoc
     */
    public function getCouponFactory($cart, $coupon)
    {
        return new CouponFactory($cart, $coupon);
    }

    /**
     * @inheritdoc
     */
    public function order($order)
    {
        return $this->getOrderFactory($order)->call();
    }

    /**
     * @inheritdoc
     */
    public function getOrderFactory($order)
    {
        return new OrderFactory($order);
    }

    /**
     * @inheritdoc
     */
    public function paymentGateway($gateway)
    {
       return $this->getPaymentGatewayFactory($gateway)->call();
    }

    /**
     * @inheritdoc
     */
    public function getPaymentGatewayFactory($gateway)
    {
        return new PaymentGatewayFactory($gateway);
    }

    /**
     * @inheritDoc
     */
    public function directory()
    {
        return 'Magento';
    }

    /**
     * @inheritDoc
     */
    public function basePath()
    {
        return Mage::getBaseDir();
    }

    /**
     * @inheritDoc
     */
    public function pluginPath($path = null)
    {
        return trim(Mage::getBaseDir() . "/app/code/community/Converdo/{$path}");
    }

    /**
     * @inheritDoc
     */
    public function httpPath($path = null)
    {
        return trim(Mage::getBaseUrl(\Mage_Core_Model_Store::URL_TYPE_SKIN) . "adminhtml/default/default/conversionmonitor/{$path}");
    }
}