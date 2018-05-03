<?php

namespace Converdo\ConversionMonitor\Magento\Factories;

use Converdo\ConversionMonitor\Core\Factories\BaseSearchFactory;
use Mage;
use Mage_CatalogSearch_Model_Query;

class SearchFactory extends BaseSearchFactory
{
    /**
     * The query instance.
     *
     * @var Mage_CatalogSearch_Model_Query
     */
    protected $query;

    /**
     * SearchFactory constructor.
     *
     * @param  Mage_CatalogSearch_Model_Query   $query
     */
    public function __construct(Mage_CatalogSearch_Model_Query $query)
    {
        $this->query = $query;
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        return $this->model
                    ->setTerm($this->query->getQueryText())
                    ->setTotalResults($this->query->getNumResults())
                    ->setPageNumber(Mage::getBlockSingleton('page/html_pager')->getCurrentPage());
    }
}