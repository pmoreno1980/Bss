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
 * @package    Bss_ProductCustomTabs
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductCustomTabs\Block\Adminhtml\Tab\Edit\Button;

class AddNewTab extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{

    /**
     * Get Url
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        $storeId = $this->context->getRequestParam('store');
        $route = '*/*/new';
        $params = ['store' => $storeId];
        return $this->context->getUrl($route, $params);
    }

    /**
     * Get Button Data
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Add New Tab'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'url' => $this->getUrl()
        ];
    }
}
