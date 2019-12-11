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
 * @package    Bss_SimpledetailconfigurableCustomize
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\SimpledetailconfigurableCustomize\Plugin\Helper;

class ProductData
{
    /**
     * Plugin to add additional attribute
     *
     * @param \Bss\Simpledetailconfigurable\Helper\ProductData $subject
     * @param $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetDetailData(
        \Bss\Simpledetailconfigurable\Helper\ProductData $subject,
        $proceed,
        $product
    ) {
        $result = $proceed($product);
        $attr = $product->getResource()->getAttribute('brand');
        $brand = '';
        if ($attr->usesSource()) {
            $brand = $attr->getSource()
                ->getOptionText($product->getData('brand'));
        }
        $result['brand'] = $brand;
        $result['mpn'] = $product->getMpn();
        $result['lead_time'] = $product->getLeadTime();
        return $result;
    }
}
