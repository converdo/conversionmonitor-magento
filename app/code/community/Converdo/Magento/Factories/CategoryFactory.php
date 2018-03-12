<?php

namespace Converdo\ConversionMonitor\Magento\Factories;

use Converdo\ConversionMonitor\Core\Factories\BaseCategoryFactory;
use Mage_Catalog_Model_Category;

class CategoryFactory extends BaseCategoryFactory
{
    /**
     * The category instance.
     *
     * @var Mage_Catalog_Model_Category
     */
    protected $category;

    /**
     * CategoryFactory constructor.
     *
     * @param  Mage_Catalog_Model_Category      $category
     */
    public function __construct(Mage_Catalog_Model_Category $category)
    {
        $this->category = $category;
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        return $this->model
                    ->setName($this->category->getName());
    }
}