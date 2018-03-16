<?php

namespace Converdo\ConversionMonitor\Magento\Factories;

use Converdo\ConversionMonitor\Core\Enumerables\Pages\AccountPage;
use Converdo\ConversionMonitor\Core\Enumerables\Pages\CartPage;
use Converdo\ConversionMonitor\Core\Enumerables\Pages\CategoryPage;
use Converdo\ConversionMonitor\Core\Enumerables\Pages\CheckoutPage;
use Converdo\ConversionMonitor\Core\Enumerables\Pages\HomePage;
use Converdo\ConversionMonitor\Core\Enumerables\Pages\ProductPage;
use Converdo\ConversionMonitor\Core\Enumerables\Pages\SearchPage;
use Converdo\ConversionMonitor\Core\Enumerables\Pages\SeoPage;
use Converdo\ConversionMonitor\Core\Enumerables\Pages\SuccessPage;
use Converdo\ConversionMonitor\Core\Enumerables\PageType;
use Converdo\ConversionMonitor\Core\Factories\BasePageFactory;
use Mage;

class PageFactory extends BasePageFactory
{
    /**
     * @inheritdoc
     */
    public function build()
    {
        return $this->model
                    ->setType($this->handlePageType())
                    ->setLanguageCode($this->handleLocale());
    }

    /**
     * Parse the locale as valid ISO 639-1 locale code.
     *
     * @return string
     */
    protected function handleLocale()
    {
        return substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
    }

    /**
     * Get the page type.
     *
     * @return PageType
     */
    protected function handlePageType()
    {
        switch (strtolower(Mage::app()->getFrontController()->getRequest()->getControllerName())) {
            case 'cart':
                return new CartPage();
            case 'index':
                return new HomePage();
            case 'account':
                return new AccountPage();
            case 'success':
                return new SuccessPage();
        }

        switch (strtolower(Mage::app()->getFrontController()->getAction()->getFullActionName())) {
            case 'checkout_onepage_index':
            case 'firecheckout_index_index':
            case 'aw_onestepcheckout_index_index':
            case 'opc_index_index':
            case 'gomage_checkout_onepage_index':
            case 'anattadesign_awesomecheckout_onepage_index':
            case 'checkout_onestep_index':
            case 'onestepcheckout_index_index':
                return new CheckoutPage();
        }

        if (cvd_config()->platform()->isProduct()) {
            return new ProductPage();
        } elseif (cvd_config()->platform()->isCategory()) {
            return new CategoryPage();
        } elseif (cvd_config()->platform()->isSearch()) {
            return new SearchPage();
        }

        return new SeoPage();
    }
}