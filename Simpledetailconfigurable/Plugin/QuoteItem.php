<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Plugin;

class QuoteItem
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    private $helper;

    /**
     * QuoteItem constructor.
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param array $result
     * @return array
     */
    public function afterToArray(
        \Magento\Quote\Model\Quote\Item $subject,
        $result
    ) {
        if ($this->helper->isShowName() && $child = $this->getChildProduct($subject)) {
            $result['name'] = $child->getName();
        }
        return $result;
    }

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject
     * @return mixed|null
     */
    private function getChildProduct($subject)
    {
        if ($subject->getProductType() !== 'configurable') {
            return null;
        }
        if ($simpleOption = $subject->getOptionByCode('simple_product')) {
            return $simpleOption->getProduct();
        }
        return null;
    }
}
