<?php

require_once __DIR__ . '/../../Core/bootstrap.php';

class Converdo_Magento_InformationController extends \Mage_Core_Controller_Front_Action
{
    /**
     * Get the plugin information.
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function informationAction()
    {
        $plugin = $this->buildPluginInformation();

        $logs = $this->buildLogOutput();

        $gateways = $this->buildPaymentGatewayInformation();

        return $this->getResponse()
                    ->setHeader('Content-type', 'application/json')
                    ->setBody(json_encode(array_filter(compact('plugin', 'logs', 'gateways'))));
    }

    /**
     * Build the public plugin information.
     *
     * @return array
     */
    protected function buildPluginInformation()
    {
        return [
            'version' => cvd_config()->version(),
        ];
    }

    /**
     * Build the plugin log file output.
     *
     * @return array
     */
    protected function buildLogOutput()
    {
        if ($this->cannotSeePrivateInformation()) {
            return [];
        }

        return cvd_logger()->tail(100);
    }

    /**
     * Determine if private information can be seen in the response.
     *
     * @return bool
     */
    protected function canSeePrivateInformation()
    {
        return isset($_GET['key']) && $_GET['key'] === cvd_config()->platform()->encryption();
    }

    /**
     * Determine if private information cannot be seen in the response.
     *
     * @return bool
     */
    protected function cannotSeePrivateInformation()
    {
        return ! $this->canSeePrivateInformation();
    }

    /**
     * Build the payment gateway information.
     *
     * @return array
     */
    protected function buildPaymentGatewayInformation()
    {
        if ($this->cannotSeePrivateInformation()) {
            return [];
        }

        $gateways = [];

        foreach (Mage::getModel('payment/config')->getActiveMethods() as $gateway) {
            $gateways[] = cvd_config()->platform()->paymentGateway($gateway)->render();
        }

        return $gateways;
    }
}