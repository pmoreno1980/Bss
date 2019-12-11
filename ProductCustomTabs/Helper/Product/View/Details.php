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
 * @package    BSS_ProductCustomTabs
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductCustomTabs\Helper\Product\View;

class Details extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Helper Config Admin
     *
     * @var \Bss\ProductCustomTabs\Helper\Config\ConfigAdmin $helperConfigAdmin
     */
    protected $helperConfigAdmin;

    /**
     * Http
     *
     * @var \Magento\Framework\App\Request\Http $request
     */
    protected $request;

    /**
     * Details constructor.
     *
     * @param \Bss\ProductCustomTabs\Helper\Config\ConfigAdmin $helperConfigAdmin
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Helper\Context $context
     *
     * @return void
     */
    public function __construct(
        \Bss\ProductCustomTabs\Helper\Config\ConfigAdmin $helperConfigAdmin,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->helperConfigAdmin = $helperConfigAdmin;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Get Template
     *
     * @return string
     */
    public function getTemplate()
    {
        if ($this->helperConfigAdmin->getConfigEnableModule()) {
            $template = 'Bss_ProductCustomTabs::product/view/details.phtml';
        } else {
            $template = 'Magento_Catalog::product/view/details.phtml';
        }
        return $template;
    }
}
