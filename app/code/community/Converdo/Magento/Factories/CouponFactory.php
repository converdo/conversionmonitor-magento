<?php

namespace Converdo\ConversionMonitor\Magento\Factories;

use Converdo\ConversionMonitor\Core\Enumerables\Coupons\FixedCartType;
use Converdo\ConversionMonitor\Core\Enumerables\Coupons\FixedProductType;
use Converdo\ConversionMonitor\Core\Enumerables\Coupons\PercentageProductType;
use Converdo\ConversionMonitor\Core\Enumerables\CouponType;
use Converdo\ConversionMonitor\Core\Factories\BaseCouponFactory;
use Mage;
use Mage_Sales_Model_Quote;
use Mage_SalesRule_Model_Coupon;
use Mage_SalesRule_Model_Rule;

class CouponFactory extends BaseCouponFactory
{
    /**
     * The cart instance.
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $cart;

    /**
     * The coupon instance.
     *
     * @var Mage_SalesRule_Model_Coupon
     */
    protected $coupon;

    /**
     * The coupon sales rule instance.
     *
     * @var Mage_SalesRule_Model_Rule
     */
    protected $rule;

    /**
     * CouponFactory constructor.
     *
     * @param  Mage_Sales_Model_Quote       $cart
     * @param  Mage_SalesRule_Model_Coupon  $coupon
     */
    public function __construct(Mage_Sales_Model_Quote $cart, Mage_SalesRule_Model_Coupon $coupon)
    {
        $this->cart = $cart;

        $this->coupon = $coupon;

        $this->rule = Mage::getModel('salesrule/rule')->load($this->coupon->getRuleId());
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        return $this->model
                    ->setCoupon($this->coupon->getCode())
                    ->setType($this->handleCouponType())
                    ->setAmount($this->handleAmount())
                    ->setMinimumCartTotal($this->findConditionValue('base_subtotal', '>'))
                    ->setMaximumCartTotal($this->findConditionValue('base_subtotal', '<'))
                    ->setFreeShipping($this->rule->getSimpleFreeShipping());
    }

    /**
     * Find a condition in the coupon sales rule.
     *
     * @param  string       $name
     * @param  string       $operator
     * @return mixed
     */
    protected function findConditionValue($name, $operator)
    {
        $conditions = unserialize($this->rule->getConditionsSerialized());

        if (isset($conditions['conditions'])) {
            foreach ($conditions['conditions'] as $condition) {
                if ($condition['attribute'] === $name && $condition['operator'] === $operator) {
                    return $condition['value'];
                }
            }
        }

        return null;
    }

    /**
     * Get the coupon type.
     *
     * @return CouponType
     */
    protected function handleCouponType()
    {
        switch (strtolower($this->rule->getSimpleAction())) {
            case Mage_SalesRule_Model_Rule::BY_FIXED_ACTION:
            case Mage_SalesRule_Model_Rule::TO_FIXED_ACTION:
                return new FixedProductType();
            case Mage_SalesRule_Model_Rule::CART_FIXED_ACTION:
                return new FixedCartType();
            default:
                return new PercentageProductType();
        }
    }

    /**
     * Get the total discount amount.
     *
     * @return float
     */
    protected function handleAmount()
    {
        return $this->cart->getSubtotal() - $this->cart->getBaseSubtotalWithDiscount();
    }
}