<?php

namespace Converdo\ConversionMonitor\Magento\Factories;

use Converdo\ConversionMonitor\Core\Enumerables\Products\BundledProduct;
use Converdo\ConversionMonitor\Core\Enumerables\Products\ConfigurableProduct;
use Converdo\ConversionMonitor\Core\Enumerables\Products\DownloadableProduct;
use Converdo\ConversionMonitor\Core\Enumerables\Products\GroupedProduct;
use Converdo\ConversionMonitor\Core\Enumerables\Products\SimpleProduct;
use Converdo\ConversionMonitor\Core\Enumerables\Products\VirtualProduct;
use Converdo\ConversionMonitor\Core\Enumerables\ProductType;
use Converdo\ConversionMonitor\Core\Factories\BaseProductFactory;
use Mage;
use Mage_Catalog_Model_Product;

class ProductFactory extends BaseProductFactory
{
    /**
     * The product instance.
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $product;

    /**
     * The product parent instance.
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $parent;

    /**
     * The product quantity.
     *
     * @var int
     */
    protected $quantity;

    /**
     * ProductFactory constructor.
     *
     * @param  Mage_Catalog_Model_Product   $product
     * @param  int                          $quantity
     */
    public function __construct(Mage_Catalog_Model_Product $product, $quantity = null)
    {
        $this->product = $product;

        $this->quantity = $quantity;

        $this->parent = Mage::getModel('catalog/product')->load($this->product->getId());
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        return $this->model
                    ->setName($this->getPrioritizedInstance()->getName())
                    ->setSku($this->getPrioritizedInstance()->getSku())
                    ->setCost($this->handleAttributeSafely('cost'))
                    ->setPrice((float) $this->product->getFinalPrice())
                    ->setBrand($this->handleAttributeSafely('manufacturer'))
                    ->setImage((string) $this->handleImageUrlString())
                    ->setType($this->handleProductType())
                    ->setCategories($this->handleCategories())
                    ->setAttributes($this->handleAttributes())
                    ->setQuantity($this->quantity);
    }

    /**
     * Get the product type.
     *
     * @return ProductType
     */
    protected function handleProductType()
    {
        switch ($this->product->getTypeId()) {
            case 'grouped':
                return new GroupedProduct();
            case 'configurable':
                return new ConfigurableProduct();
            case 'virtual':
                return new VirtualProduct();
            case 'bundle':
                return new BundledProduct();
            case 'downloadable':
                return new DownloadableProduct();
            default:
                return new SimpleProduct();
        }
    }

    /**
     * Get the attribute text safely.
     *
     * @param  string           $variable
     * @return string
     */
    protected function handleAttributeSafely($variable)
    {
        $manufacturer = $this->product->getAttributeText($variable);

        return $manufacturer ?: null;
    }

    /**
     * Get the URL of the product image.
     *
     * @return string
     */
    protected function handleImageUrlString()
    {
        return Mage::helper('catalog/image')->init($this->product, 'thumbnail');
    }

    /**
     * Get the product attributes name.
     *
     * @return string
     */
    protected function handleAttributes()
    {
        $attributeSetModel = Mage::getModel("eav/entity_attribute_set");

        $attributeSetModel->load($this->product->getAttributeSetId());

        return $attributeSetModel->getData('attribute_set_name');
    }

    /**
     * Get the product category names from the category ids.
     *
     * @return array
     */
    protected function handleCategories()
    {
        $categories = [];

        $level = 0;

        foreach (array_filter($this->product->getCategoryIds()) as $key => $id) {
            $category = Mage::getModel('catalog/category')->load($id);

            if ($category->getData('include_in_menu') != 1 || $category->getData('is_active') != 1) {
                continue;
            }

            $categories[] = [
                'level' => (int) $category->getData('level'),
                'name' => $category->getName(),
            ];
        }

        usort($categories, function ($a, $b) {
            return $a['level'] < $b['level'];
        });

        if (count($categories)) {
            $level = (int) $categories[0]['level'];
        }

        foreach ($categories as $key => $category) {
            if ($category['level'] < $level || count($categories) > 5) {
                unset($categories[$key]);

                continue;
            }

            $categories[$key] = $category['name'];
        }

        return $categories;
    }

    /**
     * Get the prioritized product instance.
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function getPrioritizedInstance()
    {
        return $this->parent ? $this->parent : $this->product;
    }
}