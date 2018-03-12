<?php

require_once __DIR__ . '/../../Core/bootstrap.php';

use Converdo\ConversionMonitor\Core\Controllers\BaseInformationController;

class Converdo_Magento_InformationController extends \Mage_Core_Controller_Front_Action
{
    /**
     * Get the plugin information.
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function informationAction()
    {
        return $this->getResponse()
                    ->setHeader('Content-type', 'application/json')
                    ->setBody(json_encode(cvd_app(BaseInformationController::class)->information()));
    }
}