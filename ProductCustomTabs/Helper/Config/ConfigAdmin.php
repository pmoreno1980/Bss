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
namespace Bss\ProductCustomTabs\Helper\Config;

use \Magento\Framework\App\Helper\AbstractHelper;

class ConfigAdmin extends AbstractHelper
{

    /**
     * Const config enable module
     */
    const BSS_CONFIG_ENABLE_MODULE = 'bss_product_custom_tabs/setting/enable';

    /**
     * Const config config show blocks related products
     */
    const BSS_SHOW_RELATED_PRODUCTS = 'bss_product_custom_tabs/setting/show_related_product';

    /**
     * Const config hide default product tabs
     */
    const BSS_HIDE_DEFAULT_TABS = 'bss_product_custom_tabs/setting/hide_default_tabs';

    //General config admin
    /**
     * Get Config Enable Module
     *
     * @return string
     */
    public function getConfigEnableModule()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CONFIG_ENABLE_MODULE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getConfigHideDefaultTabs()
    {
        return $this->scopeConfig->getValue(
            self::BSS_HIDE_DEFAULT_TABS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Show Related Product
     *
     * @return string
     */
    public function getConfigShowRelatedProduct()
    {
        return $this->scopeConfig->getValue(
            self::BSS_SHOW_RELATED_PRODUCTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
